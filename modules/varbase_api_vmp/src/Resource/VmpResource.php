<?php

declare(strict_types = 1);

namespace Drupal\varbase_api_vmp\Resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RenderContext;
use Drupal\jsonapi\ResourceResponse;
use Drupal\jsonapi_resources\Exception\ResourceImplementationException;
use Drupal\jsonapi_resources\Resource\EntityResourceBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * JSON:API Resource to return VMP results.
 */
final class VmpResource extends EntityResourceBase implements ContainerInjectionInterface {

  /**
   * The resource type repository.
   *
   * @var \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs a new VmpResource.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(EntityRepositoryInterface $entity_repository) {
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository')
    );
  }

  /**
   * Process the resource request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $id
   *   Entity.
   *
   * @return \Drupal\jsonapi\ResourceResponse
   *   The response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function process(Request $request, $id): ResourceResponse {
    $vmp = $this->entityRepository->loadEntityByUuid('varbase_api_vmp', $id);

    if (empty($vmp)) {
      throw new ResourceImplementationException('Not Found');
    }

    $blocks = $vmp->blocks->getValue();
    $primary_data = [];
    $entities = [];
    $meta = [
      'title' => $vmp->label(),
    ];

    foreach ($blocks as $key => &$block) {
      $view = Views::getView($block['target_id']);
      assert($view instanceof ViewExecutable);
      $display_id = $block['display_id'];
      $id = $block['target_id'] . '-' . $display_id;
      unset($block['data']);

      if (!$view->access([$display_id])) {
        return $this->createJsonapiResponse($this->createCollectionDataFromEntities([]), $request, 403, []);
      }

      $context = new RenderContext();
      \Drupal::service('renderer')->executeInRenderContext($context, function () use (&$view, $display_id, $request) {
        return $this->executeView($view, $display_id, $request);
      });

      // Handle any bubbled cacheability metadata.
      if (!$context->isEmpty()) {
        $bubbleable_metadata = $context->pop();
        BubbleableMetadata::createFromObject($view->result)
          ->merge($bubbleable_metadata);
      }

      $block_entities = array_map(function (ResultRow $row) {
        return $row->_entity;
      }, $view->result);

      foreach ($block_entities as $item) {
        $block['entities'][] = $item->uuid();
      }

      $entities = array_merge($entities, $block_entities);

      if (empty($block_entities)) {
        unset($blocks[$key]);
      }
    }
    $meta['blocks'] = array_values($blocks);

    $primary_data = $this->createCollectionDataFromEntities($entities, $id);
    $cacheability = new CacheableMetadata();
    $cacheability->addCacheContexts(['url']);
    $response = $this->createJsonapiResponse($primary_data, $request, 200, [], NULL, $meta);
    $response->addCacheableDependency($cacheability);
    return $response;
  }

  /**
   * Executes a view display with url parameters.
   *
   * @param \Drupal\views\ViewExecutable\ViewExecutable $view
   *   An executable view instance.
   * @param string $display_id
   *   A display machine name.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Drupal\views\ViewExecutable\ViewExecutable
   *   The executed view with query parameters applied as exposed filters.
   */
  protected function executeView(ViewExecutable &$view, string $display_id, Request $request) {
    return $view->preview($display_id);
  }

}
