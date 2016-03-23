<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentStatus\PaymentStatusListBuilder.
 */

namespace Drupal\payment_offsite_api\Entity\PaymentStatus;

use Drupal\Core\Entity\EntityListBuilder;

/**
 * Lists payment_status entities.
 */
class PaymentExternalStatusListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function render() {
    // Payment statuses are displayed by a custom controller. This list builder
    // is used solely for entity operations.
    throw new \Exception('This class is only used for entity operations and not for building lists.');
  }
}
