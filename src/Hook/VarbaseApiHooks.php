<?php

declare(strict_types=1);

namespace Drupal\varbase_api\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\jsonapi_extras\Entity\JsonapiResourceConfig;
use Drupal\views\ViewEntityInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Hook implementations for the Varbase API module.
 */
class VarbaseApiHooks {

  use StringTranslationTrait;

  /**
   * Constructs a VarbaseApiHooks object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $routeBuilder
   *   The route builder.
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   *   The route provider.
   * @param \Drupal\Core\Config\ConfigInstallerInterface $configInstaller
   *   The config installer.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param object $resourceTypeRepository
   *   The JSON:API resource type repository.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger channel factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    #[Autowire(service: 'router.builder')]
    protected RouteBuilderInterface $routeBuilder,
    #[Autowire(service: 'router.route_provider')]
    protected RouteProviderInterface $routeProvider,
    #[Autowire(service: 'config.installer')]
    protected ConfigInstallerInterface $configInstaller,
    protected AccountProxyInterface $currentUser,
    #[Autowire(service: 'jsonapi.resource_type.repository')]
    protected $resourceTypeRepository,
    protected LoggerChannelFactoryInterface $loggerFactory,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Implements hook_entity_insert().
   */
  #[Hook('entity_insert')]
  public function entityInsert(EntityInterface $entity): void {
    $entity_json_operation = $this->configFactory->get('varbase_api.settings')->get('entity_json');
    $entity_type_bundle = $entity->getEntityType()->getBundleOf();

    if ($entity_type_bundle && $entity_json_operation) {
      $this->routeBuilder->rebuild();
      $this->routeProvider->reset();
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_presave() for the view entity type.
   */
  #[Hook('view_presave')]
  public function viewPresave(ViewEntityInterface $view): void {
    if ($this->configInstaller->isSyncing()) {
      return;
    }
    elseif ($view->id() === 'content' && $view->isNew()) {
      $display = &$view->getDisplay('default');

      // If the Operations field's 'Include destination' switch is enabled, it
      // will force every operation to have the 'destination' query parameter,
      // which breaks JSON API 2.0, causing the 'View JSON' operation to throw a
      // BadRequestHttpException.
      if (isset($display['display_options']['fields']['operations']['destination'])) {
        $display['display_options']['fields']['operations']['destination'] = FALSE;
      }
    }
  }

  /**
   * Implements hook_entity_operation().
   */
  #[Hook('entity_operation')]
  public function entityOperation(EntityInterface $entity): array {
    $operations = [];

    if ($this->currentUser->hasPermission('access view json entity operation')
      && $this->configFactory->get('varbase_api.settings')->get('entity_json')
      && $this->isJsonapiResourceConfigEnabled($entity)) {

      $view_json_operation_url = $this->getViewJsonOperationUrl($entity);
      if (isset($view_json_operation_url) && $view_json_operation_url !== '') {
        $operations['view-json'] = [
          'title' => $this->t('View JSON'),
          'url' => $view_json_operation_url,
          'weight' => 90,
          'query' => ['destination' => []],
        ];
      }
    }

    if ($this->currentUser->hasPermission('access view api docs entity operation')
      && $this->currentUser->hasPermission('access openapi api docs')
      && $this->configFactory->get('varbase_api.settings')->get('bundle_docs')
      && $this->isJsonapiResourceConfigEnabled($entity)) {

      $view_api_docs_operation_url = $this->getViewApiDocsOperationUrl($entity);
      if (isset($view_api_docs_operation_url) && $view_api_docs_operation_url !== '') {
        $operations['api-documentation'] = [
          'title' => $this->t('View API Docs'),
          'url' => $view_api_docs_operation_url,
          'weight' => 95,
          'query' => ['destination' => []],
        ];
      }
    }

    return $operations;
  }

  /**
   * Implements hook_form_FORM_ID_alter() for the oauth2_token_settings form.
   */
  #[Hook('form_oauth2_token_settings_alter')]
  public function formOauth2TokenSettingsAlter(array &$form, FormStateInterface $form_state, $form_id): void {
    // The key generation form provided by Simple OAuth doesn't generate unique
    // key names (or allow the user to override the names) and doesn't allow the
    // user to specify the location of the OpenSSL config file. Specifically, the
    // fact that the names are always the same could cause problems on systems
    // where the home directory stores keys for more than one application. So
    // hide the link to that form and users can continue to use the one provided
    // by Varbase API.
    $form['actions']['generate']['keys']['#access'] = FALSE;
  }

  /**
   * Implements hook_entity_bundle_create().
   */
  #[Hook('entity_bundle_create')]
  public function entityBundleCreate($entity_type_id, $bundle): void {
    $auto_enabled_entity_types = (array) $this->configFactory->get('varbase_api.settings')->get('auto_enabled_entity_types');

    if (isset($auto_enabled_entity_types)
      && !in_array($entity_type_id, $auto_enabled_entity_types)) {

      // Get the JSON:API resource type.
      $resource_config_id = sprintf('%s--%s', $entity_type_id, $bundle);
      $existing_entity = $this->resourceTypeRepository->getByTypeName($resource_config_id);

      if (isset($existing_entity)) {

        JsonapiResourceConfig::create([
          'id' => $resource_config_id,
          'disabled' => FALSE,
          'path' => $entity_type_id . '/' . $bundle,
          'resourceType' => $resource_config_id,
          'resourceFields' => [],
        ])->save();

        $config_factory = $this->configFactory->getEditable('jsonapi_extras.jsonapi_resource_config.' . $resource_config_id);

        if (isset($config_factory)) {
          $config_factory->set('disabled', TRUE)->save();
          $this->resourceTypeRepository->reset();
          $logger_values = [
            '@entity_type_id' => $entity_type_id,
            '@bundle' => $bundle,
          ];
          $this->loggerFactory->get('varbase_api')->info('Disabled JSON:API Endpoint for Entity Type: @entity_type_id Bundle: @bundle', $logger_values);
        }
      }
    }
  }

  /**
   * Checks if the JSON:API resource config was enabled for this entity.
   */
  protected function isJsonapiResourceConfigEnabled(EntityInterface $entity): bool {
    // Get the JSON:API resource type.
    $resource_config_id = sprintf('%s--%s', $entity->getEntityTypeId(), $entity->bundle());
    $resource_type_existing_entity = $this->resourceTypeRepository->getByTypeName($resource_config_id);

    if (isset($resource_type_existing_entity)) {
      $jsonapi_resource_config_disabled = $this->configFactory->get('jsonapi_extras.jsonapi_resource_config.' . $resource_config_id)->get('disabled');
      if (!$jsonapi_resource_config_disabled) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Gets the View JSON:API operation URL for this entity.
   */
  protected function getViewJsonOperationUrl(EntityInterface $entity) {
    $view_json_operation_url = '';

    $uuid = $entity->uuid();
    $entity_type_id = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    /** @var \Drupal\jsonapi\ResourceType\ResourceType $resource_type */
    $resource_type = $this->resourceTypeRepository
      ->get(
        $entity_type_id,
        ($bundle) ? $bundle : ''
      );

    if ($uuid && $resource_type) {

      // JSON API routes are built dynamically per entity bundle. If for whatever
      // reason the appropriate route does not exist yet, fail silently.
      // @see self::entityInsert().
      try {
        $route_name = 'jsonapi.' . $resource_type->getTypeName() . '.individual';
        $this->routeProvider->getRouteByName($route_name);
        $route_parameters = [
          'entity' => $uuid,
        ];
        $view_json_operation_url = Url::fromRoute($route_name, $route_parameters);
      }
      catch (RouteNotFoundException $e) {
        // No worries. The route will probably be built soon, most likely during
        // the next general cache rebuild.
      }
    }

    return $view_json_operation_url;
  }

  /**
   * Gets the View API Docs operation URL for this entity.
   */
  protected function getViewApiDocsOperationUrl(EntityInterface $entity) {
    $view_api_docs_operation_url = '';

    $entity_type_id = $entity->getEntityTypeId();
    $bundle = $entity->bundle();
    $entity_type_label = $this->entityTypeManager->getDefinition($entity_type_id)->getLabel();
    $bundle_type_id = $entity->getEntityType()->getBundleEntityType();

    $fragment = '';
    if (empty($bundle_type_id)) {
      $fragment = str_replace(' ', '-', sprintf('tag/%s', $entity_type_label));
    }
    else {
      $bundle_type_label = $this->entityTypeManager->getStorage($bundle_type_id)->load($bundle)->label();
      $fragment = str_replace(' ', '-', sprintf('tag/%s-%s', $entity_type_label, $bundle_type_label));
    }

    $route_parameters = [
      'openapi_ui' => 'redoc',
      // 'openapi_ui' => 'swagger',
      'openapi_generator' => 'jsonapi',
    ];

    try {
      $view_api_docs_operation_url = Url::fromRoute('openapi.documentation', $route_parameters, ['fragment' => $fragment]);
    }
    catch (RouteNotFoundException $e) {
      // No worries. The route will probably be built soon, most likely during
      // the next general cache rebuild.
    }

    return $view_api_docs_operation_url;
  }

}
