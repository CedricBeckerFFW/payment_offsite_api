<?php
/**
 * Created by PhpStorm.
 * User: niko
 * Date: 11.03.16
 * Time: 19:29
 */

namespace Drupal\payment_offsite_api\Plugin\Payment\Method;


use Drupal\Component\Utility\Unicode;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Drupal\payment\EventDispatcherInterface;
use Drupal\payment\OperationResult;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodBase;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Drupal\payment\Response\Response;

define('PAYMENT_OFFSITE_SIGN_IN', 'IN');
define('PAYMENT_OFFSITE_SIGN_OUT', 'OUT');

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
   * @var array
   */
  private $payment_form_data = [];

  /**
   * @var array
   */
  private $ipn_required_keys = [];

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
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ModuleHandlerInterface $module_handler, EventDispatcherInterface $event_dispatcher, Token $token, PaymentStatusManagerInterface $payment_status_manager) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition, $module_handler, $event_dispatcher, $token, $payment_status_manager);
    $this->request = \Drupal::request();
    $this->logger = \Drupal::service('payment.logger');
  }

  /**
   * IPN required keys getter.
   *
   * @return array
   *   Required keys array.
   */
  public function getIpnRequiredKeys() {
    return $this->ipn_required_keys;
  }

  /**
   * IPN required keys setter.
   *
   * @param array $required_keys
   *   Required keys array.
   */
  public function setIpnRequiredKeys($required_keys) {
    $this->ipn_required_keys = $required_keys;
  }

  /**
   * Add IPN required key.
   *
   * @param string $key
   *   Required key name.
   */
  public function addIpnRequiredKey($key) {
    $this->ipn_required_keys[] = $key;
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
   * AautoSubmit flag getter.
   *
   * @return bool
   *   TRUE if autosubmit required FALSE otherwise.
   */
  public function getAutoSubmit() {
    return $this->autoSubmit;
  }

  /**
   * AautoSubmit flag setter.
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
  abstract protected function paymentForm();

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
   * Performs signature generation.
   *
   * @return string
   *   Generated signature.
   */
  abstract protected function getSignature($signature_type = PAYMENT_OFFSITE_SIGN_IN);

  /**
   * Allowed Performs signature generation.
   *
   * @return array
   *   Allowed payment method external statuses array keyed by machine name.
   */
  abstract public function getAllowedExternalStatuses();

  /**
   * Allowed Performs signature generation.
   *
   * @return string
   *   Allowed payment method external statuses array keyed by machine name.
   */
  abstract protected function getMerchantIdName();

  /**
   * Transaction ID name getter.
   *
   * @return string
   *   Transaction ID name.
   */
  abstract protected function getTransactionIdName();

  /**
   * Amount name getter.
   *
   * @return string
   *   Amount name.
   */
  abstract protected function getAmountName();

  /**
   * Signature name getter.
   *
   * @return string
   *   Signature name.
   */
  abstract protected function getSignatureName();

  /**
   * Signature name getter.
   *
   * @return array
   *   Signature name.
   */
  abstract protected function getRequiredKeys();

  /**
   * @return mixed
   */
  abstract protected function isConfigured();

  /**
   * IPN/Interaction/Process/Result validator.
   *
   * @return mixed
   */
  protected function ipnValidate() {
    $validators = $this->getValidators();

    $required_keys = $this->getRequiredKeys();
    $this->setIpnRequiredKeys($required_keys);

    foreach ($validators as $validator) {
      $validate_method_name = 'validate' . $validator;
      if (!method_exists($this, $validate_method_name)) {
        \Drupal::logger('interkassa_payment')->log(
          RfcLogLevel::WARNING,
          'Validator !method not exists',
          ['!method' => $validate_method_name]
        );
        return FALSE;
      }

      if (!$this->$validate_method_name()) {
        \Drupal::logger('interkassa_payment')->log(
          RfcLogLevel::WARNING,
          'Validator !method return FALSE',
          ['!method' => $validate_method_name]
        );
        return FALSE;
      }
    }
    return TRUE;
  }

  protected function getValidators() {
    return [
      'Empty',
      'RequiredKeys',
      'Merchant',
      'Signature',
      'TransactionId',
      'Amount',
    ];
  }

  /**
   * Form hidden items generator.
   *
   * @param array $form_data
   *   Hidden form data.
   *
   * @return array
   *   Form hidden.
   */
  protected function generateForm() {
    $form_data = $this->getPaymentFormData();
    $form = array();

    foreach ($form_data as $key => $value) {
      $form[$key] = array(
        '#type' => 'hidden',
        '#value' => $value,
      );
    }

    return $form;
  }

  protected function validateEmpty() {
    // Exit now if the $_POST was empty.
    if (empty($this->request->request->keys())) {
      \Drupal::logger('interkassa_payment')->log(
        RfcLogLevel::WARNING,
        'Interaction URL accessed with no POST data submitted.',
        []
      );
      return FALSE;
    }
    return TRUE;

  }

  protected function validateRequiredKeys() {
    $unavailable_required_keys = array_diff($this->getIpnRequiredKeys(), $this->request->request->keys());
    if (!empty($unavailable_required_keys)) {
      \Drupal::logger('interkassa_payment')->log(
        RfcLogLevel::WARNING,
        'Missing POST keys. POST data: <pre>!data</pre>',
        ['!data' => print_r($unavailable_required_keys, TRUE)]
      );
      return FALSE;
    }
    return TRUE;

  }

  protected function validateMerchant() {
    $request_merchant = $this->request->get($this->getMerchantIdName());
    // Exit now if missing Merchant ID.
    if (!$this->isConfigured() || $request_merchant != $this->getMerchantId()) {
      \Drupal::logger('interkassa_payment')->log(
        RfcLogLevel::WARNING,
        'Missing merchant id. POST data: <pre>!data</pre>',
        ['!data' => print_r(\Drupal::request()->request, TRUE)]
      );

      return FALSE;
    }
    return TRUE;

  }

  protected function validateTransactionId() {
    $request_payment_id = $this->request->get($this->getTransactionIdName());
    $payment = \Drupal::entityTypeManager()
      ->getStorage('payment')
      ->load($request_payment_id);
    if (!$payment) {
      \Drupal::logger('interkassa_payment')->log(
        RfcLogLevel::WARNING,
        'Missing transaction id. POST data: <pre>!data</pre>',
        ['!data' => print_r($this->request->request, TRUE)]
      );
      return FALSE;
    }
    $this->setPayment($payment);
    return TRUE;
  }

  protected function validateAmount() {
    $request_amount = $this->request->get($this->getAmountName());
    if ($this->getPayment()->getAmount() != $request_amount) {
      \Drupal::logger('interkassa_payment')->log(
        RfcLogLevel::WARNING,
        'Missing transaction id amount. POST data: <pre>!data</pre>',
        ['!data' => print_r(\Drupal::request()->request, TRUE)]
      );
      return FALSE;
    }
    return TRUE;
  }

  protected function validateSignature() {
    $request_signature = $this->request->get($this->getSignatureName());
    $sign = $this->getSignature(PAYMENT_OFFSITE_SIGN_IN);
    // Exit now if missing Signature.
    if (Unicode::strtoupper($request_signature) != Unicode::strtoupper($sign)) {
      \Drupal::logger('interkassa_payment')->log(
        RfcLogLevel::WARNING,
        'Missing Signature. POST data: <pre>!data</pre>',
        ['!data' => print_r($this->request->request, TRUE)]
      );
      return FALSE;
    }
    return TRUE;
  }

  protected function getMerchantId() {
    return $this->pluginDefinition[$this->getMerchantIdName()];
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentExecutionResult() {
    $response = new Response(Url::fromRoute('payment.offsite.redirect', [
      'payment' => $this->getPayment()->id()
    ]));
    return new OperationResult($response);
  }

}
