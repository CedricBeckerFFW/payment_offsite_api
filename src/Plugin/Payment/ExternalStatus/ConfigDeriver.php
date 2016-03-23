<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\Status\ConfigDeriver.
 */

namespace Drupal\payment_offsite_api\Plugin\Payment\ExternalStatus;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derives payment status plugin definitions based on configuration entities.
 */
class ConfigDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The payment status storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paymentOffsiteExternalStatusStorage;

  /**
   * Constructs a new instance.
   */
  public function __construct(EntityStorageInterface $payment_offsite_external_status_storage) {
    $this->paymentOffsiteExternalStatusStorage = $payment_offsite_external_status_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static($container->get('entity.manager')->getStorage('payment_offsite_external_status'));
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    /** @var \Drupal\payment\Entity\PaymentStatusInterface[] $statuses */
    $statuses = $this->paymentOffsiteExternalStatusStorage->loadMultiple();
    foreach ($statuses as $status) {
      $this->derivatives[$status->id()] = array(
        'description' => $status->getDescription(),
        'label' => $status->label(),
      ) + $base_plugin_definition;
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }
}
