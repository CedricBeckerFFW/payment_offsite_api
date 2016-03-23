<?php

/**
 * @file
 * Definition of Drupal\payment_offsite_api\Entity\PaymentExternalStatus.
 */

namespace Drupal\payment_offsite_api\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Defines a payment status entity.
 *
 * @ConfigEntityType(
 *   admin_permission = "payment.payment_offsite_external_status.administer",
 *   handlers = {
 *     "access" = "\Drupal\Core\Entity\EntityAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\payment_offsite_api\Entity\PaymentExternalStatus\PaymentExternalStatusForm",
 *       "delete" = "Drupal\payment_offsite_api\Entity\PaymentExternalStatus\PaymentStatusDeleteForm"
 *     },
 *     "list_builder" = "Drupal\payment_offsite_api\Entity\PaymentExternalStatus\PaymentExternalStatusListBuilder",
 *     "storage" = "\Drupal\Core\Config\Entity\ConfigEntityStorage"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "description",
 *     "id",
 *     "label",
 *     "uuid",
 *   },
 *   id = "payment_external_status",
 *   label = @Translation("Payment external status"),
 *   links = {
 *     "canonical" = "/admin/config/services/payment/external_status/edit/{payment_external_status}",
 *     "collection" = "/admin/config/services/payment/type",
 *     "edit-form" = "/admin/config/services/payment/external_status/edit/{payment_external_status}",
 *     "delete-form" = "/admin/config/services/payment/external_status/edit/{payment_external_status}/delete"
 *   }
 * )
 */
class PaymentExternalStatus extends ConfigEntityBase implements PaymentExternalStatusInterface {

  /**
   * The status' description.
   *
   * @var string
   */
  protected $description;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The entity's unique machine name.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable label.
   *
   * @var string
   */
  protected $label;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   * The entity's UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * {@inheritdoc}
   */
  public function setId($id) {
    $this->id = $id;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->label = $label;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * Sets the entity manager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *
   * @return $this
   */
  public function setEntityManager(EntityTypeManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function entityManager() {
    if (!$this->entityManager) {
      $this->entityManager = parent::entityTypeManager();
    }

    return $this->entityManager;
  }

  /**
   * Sets the typed config.
   *
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config_manager
   *
   * @return $this
   */
  public function setTypedConfig(TypedConfigManagerInterface $typed_config_manager) {
    $this->typedConfigManager = $typed_config_manager;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function getTypedConfig() {
    if (!$this->typedConfigManager) {
      $this->typedConfigManager = parent::getTypedConfig();
    }

    return $this->typedConfigManager;
  }

}
