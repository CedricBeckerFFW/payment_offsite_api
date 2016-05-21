<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 08.03.16
 * Time: 0:15
 */

namespace Drupal\payment_offsite_api\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\payment\Entity\Payment;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Entity\PaymentMethodConfiguration;


class OffsiteRedirectPaymentForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'payment_offsite_redirect_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, PaymentInterface $payment = NULL) {
    $form = $payment->getPaymentMethod()->paymentForm();
    if ($payment->getPaymentMethod()->isAutoSubmit()) {
      $form['#attached']['library'][] = 'payment_offsite_api/autosubmit';
    }
    $form['#prefix'] = '<div class="payment-offsite-redirect-form">';
    $form['#suffix'] = '</div>';
    $form['#pre_render'] = [static::class . '::cleanupExtraFormItems'];


    $form['message'] = array(
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('You will be redirected to the off-site payment server to authorize the payment.') . '</p>',
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Pressed to payment gateway'),
    );
    return $form;
  }

  /**
   * Cleanup external redirect form from drupal specific items.
   *
   * @param array $form
   *   The form to clean up.
   */
  public static function cleanupExtraFormItems(array $form) {
    unset($form['form_token']);
    unset($form['form_build_id']);
    unset($form['form_id']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Unused, this is redirect to payment gateway form.
  }
}
