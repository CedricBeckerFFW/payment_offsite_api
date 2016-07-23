<?php

namespace Drupal\payment_offsite_api\Controller;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Drupal\payment\Entity\PaymentMethodConfiguration;
use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles result pages and IPN requests.
 */
class PaymentOffsiteController extends ControllerBase {

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
   */
  protected $paymentMethodManager;

  /**
   * Constructs a new PaymentOffsiteController object.
   *
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager
   *   The payment method manager.
   */
  public function __construct(PaymentMethodManagerInterface $payment_method_manager) {
    $this->paymentMethodManager = $payment_method_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.payment.method')
    );
  }

  /**
   * Processes payment gateway responses.
   *
   * @param \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationInterface $payment_method_configuration
   *   The payment method configuration entity.
   * @param string $external_status
   *   The passed in status.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array|\Symfony\Component\HttpFoundation\Response
   *   The response or result page content.
   */
  public function content(PaymentMethodConfigurationInterface $payment_method_configuration, $external_status = '', Request $request) {
    $plugin_id = $payment_method_configuration->getPluginId() . ':' . $payment_method_configuration->id();
    $payment_method = $this->paymentMethodManager
      ->createInstance($plugin_id, $payment_method_configuration->getPluginConfiguration());

    // Process IPN as hidden.
    if ($external_status == 'ipn') {
      $ipn_result = $payment_method->ipnExecute();
      $response_message = isset($ipn_result['message']) ? $ipn_result['message'] : '';
      $response_code = isset($ipn_result['response_code']) ? $ipn_result['response_code'] : 200;
      return new Response($response_message, $response_code);
    }

    // Process any other statuses with fallback mode support.
    $external_statuses = $payment_method->getResultPages();
    if ($external_statuses[$external_status] === TRUE) {
      $payment_method->setFallbackMode(TRUE);
      $ipn_result = $payment_method->ipnExecute();
      if (!$ipn_result['status'] != 'success') {
        $response_message = isset($ipn_result['message']) ? $ipn_result['message'] : '';
        $response_code = isset($ipn_result['response_code']) ? $ipn_result['response_code'] : 200;
        return new Response($response_message, $response_code);
      }
    }

    $method = 'get' . Unicode::ucfirst($external_status) . 'Content';
    if (is_callable([$payment_method, $method])) {
      return $payment_method->$method($request, $payment_method);
    }

    // @todo Add logging of missed content callback in payment method.
    return [
      '#markup' => $this->t('Payment processed with @status', ['@status' => $external_status]),
    ];
  }

}
