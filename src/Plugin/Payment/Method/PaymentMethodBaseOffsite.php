<?php
/**
 * Created by PhpStorm.
 * User: niko
 * Date: 11.03.16
 * Time: 19:29
 */

namespace Drupal\payment_offsite_api\Plugin\Payment\Method;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Drupal\payment\EventDispatcherInterface;
use Drupal\payment\OperationResult;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodBase;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Drupal\payment\Response\Response;

/**
 * Class PaymentMethodBaseOffsite.
 * @package Drupal\payment_offsite_api\Plugin\Payment\Method
 */
abstract class PaymentMethodBaseOffsite extends PaymentMethodBase implements PaymentMethodOffsiteInterface {

  /**
   * @var bool
   */
  private $fallback_mode;

  /**
   * @var bool
   */
  private $autoSubmit = FALSE;

  /**
   * @var bool
   */
  private $verbose = FALSE;

  /**
   * @var array
   */
  private $payment_form_data = [];

  /**
   * @var array
   */
  protected $request;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new instance.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed[] $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\payment\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Utility\Token $token
   *   The token API.
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   *   The payment status manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ModuleHandlerInterface $module_handler, EventDispatcherInterface $event_dispatcher, Token $token, PaymentStatusManagerInterface $payment_status_manager, LoggerInterface $logger) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition, $module_handler, $event_dispatcher, $token, $payment_status_manager);
    $this->request = \Drupal::request();
    $this->logger = $logger;
  }

  /**
   * Payment form data getter.
   *
   * @return array
   *   Payment form data keyed by param name.
   */
  public function getPaymentFormData() {
    return $this->payment_form_data;
  }

  /**
   * Payment form data setter.
   *
   * @param array $payment_form_data
   *   Payment form data keyed by param name.
   */
  public function setPaymentFormData($payment_form_data) {
    $this->payment_form_data = $payment_form_data;
  }

  /**
   * Add payment form data.
   *
   * @param string $key
   *   Param name.
   * @param string $value
   *   Param value.
   */
  public function addPaymentFormData($key, $value) {
    $this->payment_form_data[$key] = $value;
  }

  /**
   * AutoSubmit flag getter.
   *
   * @return bool
   *   TRUE if autosubmit required FALSE otherwise.
   */
  public function getAutoSubmit() {
    return $this->autoSubmit;
  }

  /**
   * AutoSubmit flag setter.
   *
   * @param bool $auto_submit
   *   TRUE if autosubmit required FALSE otherwise.
   */
  public function setAutoSubmit($auto_submit) {
    $this->autoSubmit = $auto_submit;
  }

  /**
   * AutoSubmit flag getter.
   *
   * @return bool
   *   TRUE if autosubmit required FALSE otherwise.
   */
  public function isAutoSubmit() {
    return $this->getAutoSubmit();
  }

  /**
   * AutoSubmit flag getter.
   *
   * @return bool
   *   TRUE if autosubmit required FALSE otherwise.
   */
  public function getVerbose() {
    return $this->verbose;
  }


  /**
   * AutoSubmit flag setter.
   *
   * @param bool $verbose
   *   TRUE if verbose on FALSE otherwise.
   */
  public function setVerbose($verbose) {
    $this->verbose = $verbose;
  }

  /**
   * Verbose flag getter.
   *
   * @return bool
   *   TRUE if verbose on FALSE otherwise.
   */
  public function isVerbose() {
    return $this->getVerbose();
  }


  /**
   * Fallback mode  flag getter.
   *
   * @return bool
   *   TRUE if fallback mode IPN execution required FALSE otherwise.
   */
  public function getFallbackMode() {
    return $this->fallback_mode;
  }

  /**
   * Fallback mode flag setter.
   *
   * @param bool $fallback_mode
   *   TRUE if fallback mode execution required FALSE otherwise.
   */
  public function setFallbackMode($fallback_mode) {
    $this->fallback_mode = $fallback_mode;
  }

  /**
   * Fallback mode flag getter.
   *
   * @return bool
   *   TRUE if autosubmit required FALSE otherwise.
   */
  public function isFallbackMode() {
    return $this->getFallbackMode();
  }

  /**
   * Redirect form builder.
   *
   * @return array
   *   Form array.
   */
  abstract public function paymentForm();

  /**
   * Performs the actual IPN/Interaction/Process/Result execution.
   *
   * Example result:
   * $ipn_result = [
   * 'status' => 'fail',
   *  'message' => '',
   *   'response_code' => 200,
   *  ];
   *
   * @return array
   *   Execution result array.
   */
  abstract public function ipnExecute();

  /**
   * Allowed Performs signature generation.
   *
   * @return array
   *   Allowed payment method external statuses array keyed by machine name.
   */
  abstract public function getResultPages();

  /**
   * {@inheritdoc}
   */
  abstract public function isConfigured();

  /**
   * {@inheritdoc}
   */
  abstract public function ipnValidate();

  /**
   * {@inheritdoc}
   */
  public function getPaymentExecutionResult() {
    $response = new Response(Url::fromRoute('payment.offsite.redirect', [
      'payment' => $this->getPayment()->id()
    ]));
    return new OperationResult($response);
  }

  /**
   * Form hidden items generator.
   *
   * @return array
   *   Form hidden.
   */
  protected function generateForm() {
    $form_data = $this->getPaymentFormData();
    $form = [];

    foreach ($form_data as $key => $value) {
      $form[$key] = [
        '#type' => 'hidden',
        '#value' => $value,
      ];
    }

    return $form;
  }

}
