<?php

/**
 * @file
 * Contains varbase_api_update_8###(s) hook updates.
 */

use Vardot\Entity\EntityDefinitionUpdateManager;

/**
 * Uninstall the Simple Oauth Extras module as it was merged into Simple Oauth.
 */
function varbase_api_update_8701() {

  // Get list of enabled modules.
  $core_extension_config_factory = \Drupal::service('config.factory')->getEditable('core.extension');
  $core_extension_data = $core_extension_config_factory->get();

  // If Simple Oauth Extra still enabled after the update to ~4.0 version.
  if (isset($core_extension_data['module']['simple_oauth_extras'])) {

    // Unset the module from the modules array.
    unset($core_extension_data['module']['simple_oauth_extras']);

    // Save changes for modules.
    $core_extension_config_factory->setData($core_extension_data)->save(TRUE);

  }

  $query = \Drupal::database()->delete('key_value')
    ->condition('collection', 'system.schema')
    ->condition('name', 'simple_oauth_extras')
    ->execute();

  if (isset($query)) {
    // Entity updates to clear up any mismatched entity and/or field definitions
    // And Fix changes were detected in the entity type and field definitions.
    \Drupal::classResolver()
      ->getInstanceFromDefinition(EntityDefinitionUpdateManager::class)
      ->applyUpdates();
  }
}

/**
 * Issue #3085671: Fix the update process to uninstall.
 *
 * The Simple Oauth Extras Module as it was merged into Simple Oauth to make
 * it work without conditions.
 */
function varbase_api_update_8702() {

  $simple_oauth_extras_config_factory = \Drupal::configFactory()
    ->getEditable("simple_oauth_extras.settings");
  $simple_oauth_extras_settings = $simple_oauth_extras_config_factory->get();

  if (isset($simple_oauth_extras_settings['use_implicit'])) {
    $simple_oauth_config_factory = \Drupal::configFactory()
      ->getEditable("simple_oauth.settings");

    $simple_oauth_settings = $simple_oauth_config_factory->get();

    if (!isset($simple_oauth_settings['use_implicit'])) {
      $simple_oauth_settings['use_implicit'] = $simple_oauth_extras_settings['use_implicit'];
      $simple_oauth_config_factory->setData($simple_oauth_settings)->save(TRUE);
    }

    $simple_oauth_extras_config_factory->delete();
  }

  $query = \Drupal::database()->delete('key_value')
    ->condition('collection', 'system.schema')
    ->condition('name', 'simple_oauth_extras')
    ->execute();

  if (isset($query)) {
    // Entity updates to clear up any mismatched entity and/or field definitions
    // And Fix changes were detected in the entity type and field definitions.
    \Drupal::classResolver()
      ->getInstanceFromDefinition(EntityDefinitionUpdateManager::class)
      ->applyUpdates();
  }
}

/**
 * Issue #3151781: Add [OpenAPI for JSON:API] module ( ~2.0 ).
 *
 * After OpenAPI had been updated to ~2.0.
 */
function varbase_api_update_8703() {
  if (!\Drupal::moduleHandler()->moduleExists('openapi_jsonapi')) {
    \Drupal::service('module_installer')->install(['openapi_jsonapi'], FALSE);
  }
}

/**
 * Issue #3151784: Add [OpenAPI for REST] module ( ~2.0 ).
 *
 * After OpenAPI had been updated to ~2.0.
 */
function varbase_api_update_8704() {
  if (!\Drupal::moduleHandler()->moduleExists('openapi_rest')) {
    \Drupal::service('module_installer')->install(['openapi_rest'], FALSE);
  }
}
