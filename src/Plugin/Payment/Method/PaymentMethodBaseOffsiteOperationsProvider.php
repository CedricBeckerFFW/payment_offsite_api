<?php

/**
 * Contains \Drupal\payment_offsite_api\Plugin\Payment\Method\PaymentMethodBaseOffsiteOperationsProvider.
 */

namespace Drupal\payment_offsite_api\Plugin\Payment\Method;

use Drupal\payment\Plugin\Payment\Method\PaymentMethodConfigurationOperationsProvider;

/**
 * Provides payment_basic operations based on config entities.
 */
class PaymentMethodBaseOffsiteOperationsProvider extends PaymentMethodConfigurationOperationsProvider {

  /**
   * {@inheritdoc}
   */
  protected function getPaymentMethodConfiguration($plugin_id) {
    list(, $entity_id) = explode(':', $plugin_id);

    return $this->paymentMethodConfigurationStorage->load($entity_id);
  }

}
