<?php
/**
 * @file
 * Contains \Drupal\node\Access\NodeRevisionAccessCheck.
 */

namespace Drupal\payment_offsite_api\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
use Symfony\Component\Routing\Route;

class ExternalAccessCheck implements AccessInterface{

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $paymentStorage;

  /**
   * The node access control handler.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  protected $paymentAccess;

  /**
   * A static cache of access checks.
   *
   * @var array
   */
  protected $access = array();

  /**
   * Constructs a new NodeRevisionAccessCheck.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->paymentStorage = $entity_manager->getStorage('payment_method_configuration');
    $this->paymentAccess = $entity_manager->getAccessControlHandler('payment_method_configuration');
  }

  /**
   * Checks routing access for the node revision.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\payment\Entity\PaymentMethodConfigurationInterface $payment_method_configuration
   *   Payment method configuration instance.
   * @param string $external_status
   *   External status.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access(Route $route, AccountInterface $account, PaymentMethodConfigurationInterface $payment_method_configuration, $external_status = '') {
    $payment_method_service = \Drupal::service('plugin.manager.payment.method');
    $plugin_id = $payment_method_configuration->getPluginId() . ':' . $payment_method_configuration->id();
    $payment_method = $payment_method_service->createInstance($plugin_id, $payment_method_configuration->getPluginConfiguration());
    $external_statuses = ['ipn' => FALSE] + $payment_method->getAllowedExternalStatuses();
    return AccessResult::allowedIf(array_key_exists($external_status, $external_statuses));
  }

}
