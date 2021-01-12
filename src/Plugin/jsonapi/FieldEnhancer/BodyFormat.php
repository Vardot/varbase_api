<?php

namespace Drupal\varbase_api\Plugin\jsonapi\FieldEnhancer;

use Drupal\jsonapi_extras\Plugin\ResourceFieldEnhancerBase;
use Shaper\Util\Context;
use DOMDocument as DOM;

/**
 * Change field body format value.
 *
 * @ResourceFieldEnhancer(
 *   id = "body_format",
 *   label = @Translation("Body format"),
 *   description = @Translation("Change field body format value.")
 * )
 */
class BodyFormat extends ResourceFieldEnhancerBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'format' => 'full_html',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function doUndoTransform($data, Context $context) {
    $format = empty($resource_field_info['enhancer']['settings'])
      ? $this->getConfiguration()['format']
      : $resource_field_info['enhancer']['settings']['format'];

    $dom = new DOM();
    libxml_use_internal_errors(TRUE);
    $dom->loadHTML(mb_convert_encoding($data['value'], 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();
    $medias = $dom->getElementsByTagName('drupal-media');

    foreach ($medias as $media) {
      $media->setAttribute('data-view-mode', 'mobile_text_format');
    }

    $value = $dom->saveHTML();
    $data['processed'] = check_markup($value, $format);

    unset($data['value']);
    unset($data['format']);

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  protected function doTransform($data, Context $context) {
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getOutputJsonSchema() {
    return [
      'oneOf' => [
        ['type' => 'object'],
        ['type' => 'array'],
        ['type' => 'null'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $resource_field_info) {
    $settings = empty($resource_field_info['enhancer']['settings'])
      ? $this->getConfiguration()
      : $resource_field_info['enhancer']['settings'];

    $formats = filter_formats();
    $options = [];

    foreach ($formats as $key => $format) {
      $options[$key] = $format->label();
    }

    return [
      'format' => [
        '#type' => 'select',
        '#title' => $this->t('Format'),
        '#description' => $this->t('Available formats.'),
        '#options' => $options,
        '#default_value' => $settings['format'],
      ],
    ];
  }

}
