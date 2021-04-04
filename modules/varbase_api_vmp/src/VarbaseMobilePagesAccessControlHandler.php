<?php

namespace Drupal\varbase_api_vmp;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the varbase mobile pages entity.
 *
 * @see \Drupal\varbase_api_vmp\Entity\VarbaseMobilePages.
 */
class VarbaseMobilePagesAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view varbase mobile pages entity');

      case 'edit':
        return AccessResult::allowedIfHasPermission($account, 'edit varbase mobile pages entity');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete varbase mobile pages entity');
    }
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add varbase mobile pages entity');
  }

}
