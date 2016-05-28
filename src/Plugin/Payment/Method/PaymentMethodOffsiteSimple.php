<?php
/**
 * Created by PhpStorm.
 * User: niko
 * Date: 11.03.16
 * Time: 19:29
 */

namespace Drupal\payment_offsite_api\Plugin\Payment\Method;


use Drupal\Component\Utility\Unicode;

/**
 * Class PaymentMethodBaseOffsite
 * @package Drupal\payment_offsite_api\Plugin\Payment\Method
 */
abstract class PaymentMethodOffsiteSimple extends PaymentMethodBaseOffsite implements PaymentMethodOffsiteSimpleInterface {

  /**
   * Required keys storage.
   *
   * @var array
   */
  private $ipnRequiredKeys = [];

  /**
   * IPN required keys getter.
   *
   * @return array
   *   Required keys array.
   */
  public function getIpnRequiredKeys() {
    return $this->ipnRequiredKeys;
  }

  /**
   * IPN required keys setter.
   *
   * @param array $required_keys
   *   Required keys array.
   */
  public function setIpnRequiredKeys(array $required_keys) {
    $this->ipnRequiredKeys = $required_keys;
  }

  /**
   * Add IPN required key.
   *
   * @param string $key
   *   Required key name.
   */
  public function addIpnRequiredKey($key) {
    $this->ipnRequiredKeys[] = $key;
  }

  /**
   * Merchant ID getter.
   *
   * @return string
   *   Merchant ID.
   */
  protected function getMerchantId() {
    return $this->pluginDefinition['config'][$this->getMerchantIdName()];
  }

  /**
   * Performs signature generation.
   *
   * @param string $signature_type
   *   Signature type.
   *
   * @return string
   *   Generated signature.
   */
  abstract public function getSignature($signature_type = self::PAYMENT_OFFSITE_SIGN_IN);

  /**
   * Allowed Performs signature generation.
   *
   * @return string
   *   Allowed payment method external statuses array keyed by machine name.
   */
  abstract public function getMerchantIdName();

  /**
   * Transaction ID name getter.
   *
   * @return string
   *   Transaction ID name.
   */
  abstract public function getTransactionIdName();

  /**
   * Amount name getter.
   *
   * @return string
   *   Amount name.
   */
  abstract public function getAmountName();

  /**
   * Signature name getter.
   *
   * @return string
   *   Signature name.
   */
  abstract public function getSignatureName();

  /**
   * Signature name getter.
   *
   * @return array
   *   Signature name.
   */
  abstract public function getRequiredKeys();


  /**
   * {@inheritdoc}
   */
  public function ipnValidate() {
    $validators = $this->getValidators();

    $required_keys = $this->getRequiredKeys();
    $this->setIpnRequiredKeys($required_keys);

    foreach ($validators as $validator) {
      if (!method_exists($this, $validator)) {
        // @todo replace with throw exception.
        $this->logger->warning('Validator @method not exists',
          ['@method' => $validator]
        );
        return FALSE;
      }

      if (!$this->$validator()) {
        // @todo replace with throw exception.
        $this->logger->warning('Validator @method return FALSE',
          ['@method' => $validator]
        );
        return FALSE;
      }
    }
    return TRUE;
  }


  /**
   * Validators names array for  ipnValidateDefault helper.
   *
   * @return array
   *   Validate method names array.
   */
  protected function getValidators() {
    return [
      'validateEmpty',
      'validateRequiredKeys',
      'validateMerchant',
      'validateSignature',
      'validateTransactionId',
      'validateAmount',
    ];
  }

  /**
   * Empty default validator.
   *
   * @return bool
   *   TRUE on successful validation FALSE otherwise.
   */
  protected function validateEmpty() {
    // Exit now if the $_POST was empty.
    if (empty($this->request->request->keys())) {
      // @todo replace with throw exception.
      $this->logger->warning('Interaction URL accessed with no POST data submitted.',
        []
      );
      return FALSE;
    }
    return TRUE;

  }

  /**
   * Required keys default validator.
   *
   * @return bool
   *   TRUE on successful validation FALSE otherwise.
   */
  protected function validateRequiredKeys() {
    $unavailable_required_keys = array_diff($this->getIpnRequiredKeys(), $this->request->request->keys());
    if (!empty($unavailable_required_keys)) {
      // @todo replace with throw exception.
      $this->logger->warning('Missing POST keys. POST data: <pre>@data</pre>',
        ['@data' => print_r($unavailable_required_keys, TRUE)]
      );
      return FALSE;
    }
    return TRUE;

  }

  /**
   * Merchant ID default validator.
   *
   * @return bool
   *   TRUE on successful validation FALSE otherwise.
   */
  protected function validateMerchant() {
    $request_merchant = $this->request->get($this->getMerchantIdName());
    // Exit now if missing Merchant ID.
    if (!$this->isConfigured() || $request_merchant != $this->getMerchantId()) {
      // @todo replace with throw exception.
      $this->logger->warning('Missing merchant id. POST data: <pre>@data</pre>',
        ['@data' => print_r(\Drupal::request()->request, TRUE)]
      );

      return FALSE;
    }
    return TRUE;

  }

  /**
   * Transaction ID default validator.
   *
   * @return bool
   *   TRUE on successful validation FALSE otherwise.
   */
  protected function validateTransactionId() {
    $request_payment_id = $this->request->get($this->getTransactionIdName());
    $payment = \Drupal::entityTypeManager()
      ->getStorage('payment')
      ->load($request_payment_id);
    if (!$payment) {
      // @todo replace with throw exception.
      $this->logger->warning('Missing transaction id. POST data: <pre>@data</pre>',
        ['@data' => print_r($this->request->request, TRUE)]
      );
      return FALSE;
    }
    $this->setPayment($payment);
    return TRUE;
  }

  /**
   * Amount default validator.
   *
   * @return bool
   *   TRUE on successful validation FALSE otherwise.
   */
  protected function validateAmount() {
    $request_amount = $this->request->get($this->getAmountName());
    if ($this->getPayment()->getAmount() != $request_amount) {
      // @todo replace with throw exception.
      $this->logger->warning('Missing transaction id amount. POST data: <pre>@data</pre>',
        ['@data' => print_r(\Drupal::request()->request, TRUE)]
      );
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Signature default validator.
   *
   * @return bool
   *   TRUE on successful validation FALSE otherwise.
   */
  protected function validateSignature() {
    $request_signature = $this->request->get($this->getSignatureName());
    $sign = $this->getSignature(self::PAYMENT_OFFSITE_SIGN_IN);
    // Exit now if missing Signature.
    if (Unicode::strtoupper($request_signature) != Unicode::strtoupper($sign)) {
      $this->logger->warning('Missing Signature. POST data: <pre>@data</pre>',
        ['@data' => print_r($this->request->request, TRUE)]
      );
      return FALSE;
    }
    return TRUE;
  }

}
