<?php
/**
 * Created by PhpStorm.
 * User: niko
 * Date: 12.03.16
 * Time: 9:44
 */

namespace Drupal\payment_offsite_api\Annotation;


use Drupal\Component\Annotation\Plugin;

class PaymentOffsiteExternalStatus extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the external status.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The description of the external status.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;



}