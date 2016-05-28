<?php
/**
 * Created by PhpStorm.
 * User: niko
 * Date: 28.05.16
 * Time: 11:51
 */
namespace Drupal\payment_offsite_api\Plugin\Payment\Method;

/**
 * Class PaymentMethodBaseOffsite
 * @package Drupal\payment_offsite_api\Plugin\Payment\Method
 */
interface PaymentMethodOffsiteSimpleInterface extends PaymentMethodBaseOffsiteInterface{

  const PAYMENT_OFFSITE_SIGN_IN = 'IN';
  const PAYMENT_OFFSITE_SIGN_OUT = 'OUT';

  /**
   * Performs signature generation.
   *
   * @return string
   *   Generated signature.
   */
  public function getSignature($signature_type = self::PAYMENT_OFFSITE_SIGN_IN);

  /**
   * Allowed Performs signature generation.
   *
   * @return string
   *   Allowed payment method external statuses array keyed by machine name.
   */
  public function getMerchantIdName();

  /**
   * Transaction ID name getter.
   *
   * @return string
   *   Transaction ID name.
   */
  public function getTransactionIdName();

  /**
   * Amount name getter.
   *
   * @return string
   *   Amount name.
   */
  public function getAmountName();

  /**
   * Signature name getter.
   *
   * @return string
   *   Signature name.
   */
  public function getSignatureName();

  /**
   * Signature name getter.
   *
   * @return array
   *   Signature name.
   */
  public function getRequiredKeys();

  /**
   * Is mayment method configured halper.
   *
   * @return bool
   *    TRUE if payment methid configured FALSE otherwise.
   */
  public function isConfigured();
}