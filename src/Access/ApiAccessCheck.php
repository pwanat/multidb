<?php

namespace Drupal\multidb\Access;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;

/**
 * Checks access for displaying orders api.
 */
class ApiAccessCheck implements AccessInterface {

  /**
   * A access check for orders api .
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   */
  public function access(AccountInterface $account) {
    $user = User::load($account->id());
    $access_granted = $user->get('user_multidb_access')->value;
    return AccessResult::allowedIf($access_granted);
  }

}
