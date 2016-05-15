<?php
/**
 * Created by PhpStorm.
 * User: niko
 * Date: 12.03.16
 * Time: 12:55
 */

namespace Drupal\payment_offsite_api\Controller;


use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Drupal\payment\Entity\PaymentMethodConfiguration;
use Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationInterface;
use Symfony\Component\HttpFoundation\Response;

class PaymentOffsiteController extends ControllerBase{

  public function content(PaymentMethodConfiguration $payment_method_configuration, $external_status = '') {
    $request = \Drupal::request();
    $payment_method_service = \Drupal::service('plugin.manager.payment.method');
    $plugin_id = $payment_method_configuration->getPluginId() . ':' . $payment_method_configuration->id();
    $payment_method = $payment_method_service->createInstance($plugin_id, $payment_method_configuration->getPluginConfiguration());
    $external_statuses = $payment_method->getAllowedExternalStatuses();

    // Process IPN as hidden.
    if ($external_status == 'ipn') {
      $ipn_result = $payment_method->ipnExecute();
      $response_message = isset($ipn_result['message']) ? $ipn_result['message'] : '';
      $response_code = isset($ipn_result['response_code']) ? $ipn_result['response_code'] : 200;
      return new Response($response_message, $response_code);
    }

    // Process any other statuses with fallback mode support.
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
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t('Payment processed as @external_status.', array('@external_status' => $external_status)),
    ];
  }

  public function ipnContent(PaymentMethodConfiguration $payment_method_configuration, $external_status = '') {
    return new Response('', 404);

  }

}