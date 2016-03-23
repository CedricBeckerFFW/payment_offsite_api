<?php
/**
 * Created by PhpStorm.
 * User: niko
 * Date: 11.03.16
 * Time: 19:29
 */

namespace Drupal\payment_offsite_api\Plugin\Payment\Method;


use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Url;
use Drupal\payment\OperationResult;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodBase;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodCapturePaymentInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodRefundPaymentInterface;
use Drupal\payment\Response\Response;

/**
 * Class PaymentMethodBaseOffsite
 * @package Drupal\payment_offsite_api\Plugin\Payment\Method
 */
abstract class PaymentMethodBaseOffsite extends PaymentMethodBase {

  /**
   * @var bool
   */
  private $fallback_mode;

  /**
   * @var bool
   */
  private $autoSubmit = FALSE;

  /**
   * @return boolean
   */
  public function getAutoSubmit() {
    return $this->autoSubmit;
  }

  /**
   * @param boolean $autoSubmit
   */
  public function setAutoSubmit($autoSubmit) {
    $this->autoSubmit = $autoSubmit;
  }

  /**
   * @return bool
   */
  public function isAutoSubmit() {
    return $this->getAutoSubmit();
  }

  /**
   * @return bool
   */
  public function getFallbackMode() {
    return $this->fallback_mode;
  }

  /**
   * @param $fallback_mode
   */
  public function setFallbackMode($fallback_mode) {
    $this->fallback_mode = $fallback_mode;
  }

  /**
   * @return bool
   */
  public function isFallbackMode() {
    return $this->getFallbackMode();
  }

  /**
   * Redirect form builder.
   *
   * @return array
   */
  protected abstract function paymentForm();

  /**
   * IPN/Interaction/Process/Result validator.
   *
   * @return mixed
   */
  protected abstract function IpnValidate();

  /**
   * Performs the actual IPN/Interaction/Process/Result execution.
   *
   * @return mixed
   */
  protected abstract function IpnExecute();

  /**
   * Performs signature generation.
   *
   * @return string
   */
  protected abstract function getSignature();

  /**
   * Form hidden items generator.
   *
   * @param array $form_data
   *   Hidden form data.
   *
   * @return array
   *   Form hidden.
   */
  protected function generateForm($form_data = array()) {
    $form = array();
    foreach ($form_data as $key => $value) {
      $form[$key] = array(
        '#type' => 'hidden',
        '#value' => $value,
      );
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentExecutionResult() {
    $response = new Response(Url::fromRoute('payment.offsite.redirect', array('payment' => $this->getPayment()->id())));
    return new OperationResult($response);
  }

}
