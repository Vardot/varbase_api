<?php

/**
 * @file
 * Contains List of all varbase_api helpers.
 *
 * Add custom needed helper functions.
 */

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;

/**
 * Give the API documentation page a nice path alias.
 */
function varbase_api__add_api_path_alias() {
  // If the openapi_ui_redoc and jsonapi modules are installed.
  if (Drupal::service('module_handler')->moduleExists('openapi_ui_redoc')
    && Drupal::service('module_handler')->moduleExists('jsonapi')) {

    $alias_manager = \Drupal::service('path_alias.manager');
    $path = $alias_manager->getPathByAlias('/api-docs');

    if (empty($path)) {
      $route_parameters = [
        'openapi_ui' => 'redoc',
        'openapi_generator' => 'jsonapi',
      ];
      $path_alias = \Drupal::entityTypeManager()->getStorage('path_alias')->create([
        'path' => Url::fromRoute('openapi.documentation', $route_parameters)->toString(),
        'alias' => '/api-docs',
        'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      ]);
      $path_alias->save();
    }
  }
}
