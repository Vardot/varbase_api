<?php

namespace Drupal\varbase_api_vmp;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for varbase mobile pages entity.
 *
 * @ingroup varbase_api_vmp
 */
class VarbaseMobilePagesListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['description'] = [
      '#markup' => $this->t('Varbase mobile pages.'),
    ];

    $build += parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['label'] = $this->t('Title');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['label'] = $entity->label();
    return $row + parent::buildRow($entity);
  }

}
