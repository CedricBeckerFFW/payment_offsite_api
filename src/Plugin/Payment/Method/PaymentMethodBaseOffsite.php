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
   * @return array
   */
  public function getIpnRequiredKeys() {
    return $this->ipn_required_keys;
  }

  /**
   * @param array $payment_form_data
   */
  public function setIpnRequiredKeys($required_keys) {
    $this->ipn_required_keys = $required_keys;
  }

  /**
   * @param $key
   * @param $value
   */
  public function addIpnRequiredKey($key) {
    $this->ipn_required_keys[] = $key;
  }

  /**
   * @return array
   */
  public function getPaymentFormData() {
    return $this->payment_form_data;
  }

  /**
   * @param array $payment_form_data
   */
  public function setPaymentFormData($payment_form_data) {
    $this->payment_form_data = $payment_form_data;
  }

  /**
   * @param $key
   * @param $value
   */
  public function addPaymentFormData($key, $value) {
    $this->payment_form_data[$key] = $value;
  }

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
  abstract protected function paymentForm();

  /**
   * Performs the actual IPN/Interaction/Process/Result execution.
   *
   * @return mixed
   */
  abstract public function ipnExecute();

  /**
   * Performs signature generation.
   *
   * @return string
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
   * @return mixed
   */
  abstract protected function getTransactionIdName();

  /**
   * @return mixed
   */
  abstract protected function getAmountName();

  /**
   * @return mixed
   */
  abstract protected function getSignatureName();

  /**
   * @return mixed
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
    $response = new Response(Url::fromRoute('payment.offsite.redirect', array(
      'payment' => $this->getPayment()
        ->id()
    )));
    return new OperationResult($response);
  }

}
