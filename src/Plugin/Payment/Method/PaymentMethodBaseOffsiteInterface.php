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
interface PaymentMethodBaseOffsiteInterface {

  const PAYMENT_OFFSITE_SIGN_IN = 'IN';
  const PAYMENT_OFFSITE_SIGN_OUT = 'OUT';

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
  public function ipnExecute();

  /**
   * IPN/Interaction/Process/Result validator.
   *
   * @return bool
   *    TRUE on successful validation FALSE otherwise.
   */
  public function ipnValidate();

  /**
   * Allowed Performs signature generation.
   *
   * @return array
   *   Allowed payment method external statuses array keyed by machine name.
   */
  public function getResultPages();

  /**
   * Redirect form builder.
   *
   * @return array
   *   Form array.
   */
  public function paymentForm();

  /**
   * Is mayment method configured halper.
   *
   * @return bool
   *    TRUE if payment methid configured FALSE otherwise.
   */
  public function isConfigured();
}