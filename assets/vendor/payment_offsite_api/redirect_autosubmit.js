/**
 * @file
 * Automatically submit the payment redirect form.
 */

(function ($, Drupal) {
  "use strict";

  Drupal.behaviors.paymentOffsiteApi = {
    attach: function (context) {
      $('div.payment-offsite-redirect-form form', context).submit();
    }
  };
})(jQuery, Drupal);
