<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Status\ConfigOperationsProvider.
 */

namespace Drupal\payment\Plugin\Payment\Status;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityListBuilderInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\plugin\PluginOperationsProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides payment status operations payment statuses based on config entities.
 */
class ConfigOperationsProvider implements PluginOperationsProviderInterface, ContainerInjectionInterface {

  /**
   * The payment status list builder.
   *
   * @var \Drupal\Core\Entity\EntityListBuilderInterface
   */
  protected $paymentOffsiteExternalStatusListBuilder;

  /**
   * The payment status storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paymentOffsiteExternalStatusStorage;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $payment_offsite_external_status_storage
   *   The payment status storage.
   * @param \Drupal\Core\Entity\EntityListBuilderInterface $payment_offsite_external_status_list_builder
   *   The payment status list builder.
   */
  public function __construct(EntityStorageInterface $payment_offsite_external_status_storage, EntityListBuilderInterface $payment_offsite_external_status_list_builder) {
    $this->paymentOffsiteExternalStatusListBuilder = $payment_offsite_external_status_list_builder;
    $this->paymentOffsiteExternalStatusStorage = $payment_offsite_external_status_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');

    return new static($entity_manager->getStorage('payment_offsite_external_status'), $entity_manager->getListBuilder('payment_offsite_external_status'));
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations($plugin_id) {
    $entity_id = substr($plugin_id, 15);
    $entity = $this->paymentOffsiteExternalStatusStorage->load($entity_id);

    return $this->paymentOffsiteExternalStatusListBuilder->getOperations($entity);
  }
}
