<?php

namespace Drupal\payment_offsite_api\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;

/**
 * Checks access to redirect form.
 */
class RedirectAccessCheck implements AccessInterface {

  /**
   * Checks routing access for the payment.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\payment\Entity\PaymentInterface $payment
   *   The payment entity.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, PaymentInterface $payment) {
    return AccessResult::allowedIf($account->id() == $payment->getOwnerId()
      && is_subclass_of($payment->getPaymentMethod(), 'Drupal\payment_offsite_api\Plugin\Payment\Method\PaymentMethodBaseOffsite')
      && $payment->getPaymentStatus()->getPluginId() == 'payment_pending')->setCacheMaxAge(0);;
  }

}
