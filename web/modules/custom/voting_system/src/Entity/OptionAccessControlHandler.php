<?php

namespace Drupal\voting_system\Entity;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Defines the access control handler for the voting option entity type.
 */
class OptionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // Administradores têm acesso total.
    if ($account->hasPermission('administer voting system')) {
      return AccessResult::allowed();
    }

    // Verifica permissões específicas para cada operação.
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view voting options');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit voting options');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete voting options');

      default:
        return AccessResult::neutral();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'create voting options');
  }

} 