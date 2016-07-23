<?php

namespace Drupal\payment_offsite_api\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checks access to external responses.
 */
class ExternalAccessCheck implements AccessInterface {

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
   */
  protected $paymentMethodConfigurationManager;
  protected $paymentMethodManager;
  /**
   * Constructs a new ExternalAccessCheck object.
   *
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_configuration_manager
   *   The payment method manager.
   */
  public function __construct(PaymentMethodConfigurationManagerInterface $payment_method_configuration_manager, PaymentMethodManagerInterface $payment_method_manager) {
    $this->paymentMethodConfigurationManager = $payment_method_configuration_manager;
    $this->paymentMethodManager = $payment_method_manager;
  }

  /**
   * Checks routing access for the external responses.
   *
   * @param \Drupal\payment\Entity\PaymentMethodConfigurationInterface $payment_method_configuration
   *   Payment method configuration instance.
   * @param string $external_status
   *   External status.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access(PaymentMethodConfigurationInterface $payment_method_configuration, $external_status = '') {
    $plugin_id = $payment_method_configuration->getPluginId() . ':' . $payment_method_configuration->id();
    $payment_method = $this->paymentMethodManager->createInstance($plugin_id, $payment_method_configuration->getPluginConfiguration());
    if (!is_subclass_of($payment_method, 'Drupal\payment_offsite_api\Plugin\Payment\Method\PaymentMethodBaseOffsite')) {
      // Process only off-site payment methods.
      return AccessResult::forbidden();
    }
    $external_statuses = ['ipn' => FALSE] + $payment_method->getResultPages();
    return AccessResult::allowedIf(array_key_exists($external_status, $external_statuses))->setCacheMaxAge(0);
  }

}
