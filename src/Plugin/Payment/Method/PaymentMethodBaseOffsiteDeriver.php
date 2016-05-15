<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\Method\BasicDeriver.
 */

namespace Drupal\payment_offsite_api\Plugin\Payment\Method;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derives payment method plugin definitions based on configuration entities.
 *
 * @see \Drupal\payment\Plugin\Payment\Method\Basic
 */
class PaymentMethodBaseOffsiteDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The payment method configuration manager.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface
   */
  protected $paymentMethodConfigurationManager;

  /**
   * The payment method configuration storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paymentMethodConfigurationStorage;

  /**
   * Constructs a new instance.
   */
  public function __construct(EntityStorageInterface $payment_method_configuration_storage, PaymentMethodConfigurationManagerInterface $payment_method_configuration_manager) {
    $this->paymentMethodConfigurationStorage = $payment_method_configuration_storage;
    $this->paymentMethodConfigurationManager = $payment_method_configuration_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');

    return new static($entity_manager->getStorage('payment_method_configuration'), $container->get('plugin.manager.payment.method_configuration'));
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    /** @var \Drupal\payment\Entity\PaymentMethodConfigurationInterface[] $payment_methods */
    $payment_methods = $this->paymentMethodConfigurationStorage->loadMultiple();
    foreach ($payment_methods as $payment_method) {
      if ($payment_method->getPluginId() == 'payment_interkassa') {
        /** @var \Drupal\interkassa_payment\Plugin\Payment\MethodConfiguration\Interkassa $configuration_plugin */
        $configuration_plugin = $this->paymentMethodConfigurationManager->createInstance($payment_method->getPluginId(), $payment_method->getPluginConfiguration());
        $this->derivatives[$payment_method->id()] = array(
            'id' => $base_plugin_definition['id'] . ':' . $payment_method->id(),
            'active' => $payment_method->status(),
            'label' => $payment_method->label(),

            'message_text' => $configuration_plugin->getMessageText(),
            'message_text_format' => $configuration_plugin->getMessageTextFormat(),
            'new_status_id' => $configuration_plugin->getStatusId('new_status_id'),
            'wait_accept_status_id' => $configuration_plugin->getStatusId('wait_accept_status_id'),
            'success_status_id' => $configuration_plugin->getStatusId('success_status_id'),
            'process_status_id' => $configuration_plugin->getStatusId('process_status_id'),
            'canceled_status_id' => $configuration_plugin->getStatusId('canceled_status_id'),
            'fail_status_id' => $configuration_plugin->getStatusId('fail_status_id'),
            'ik_co_id' => $configuration_plugin->getCheckoutId(),
            'ik_cur' => $configuration_plugin->getDefaultCurrency(),
            'sign_key' => $configuration_plugin->getSignKey(),
            'test_key' => $configuration_plugin->getSignKey(TRUE),
            'action_url' => $configuration_plugin->getActionUrl(),
            'hash_type' => $configuration_plugin->getHashType(),
          ) + $base_plugin_definition;
      }
    }

    return $this->derivatives;
  }

}
