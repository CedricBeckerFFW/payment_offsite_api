<?php
/**
 * @file
 * Contains \Drupal\node\Access\NodeRevisionAccessCheck.
 */

namespace Drupal\payment_offsite_api\Access;


use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Annotations\PaymentMethodConfiguration;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManager;
use Drupal\plugin\PluginType\PluginType;
use Drupal\plugin\PluginType\PluginTypeInterface;
use Drupal\Tests\plugin\Unit\PluginType\PluginTypeManagerTest;
use Symfony\Component\Routing\Route;

class RedirectAccessCheck implements AccessInterface{

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
    $this->paymentMethodStorage = $entity_manager->getStorage('PaymentMethodManager');
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
   * @param int $node_revision
   *   (optional) The node revision ID. If not specified, but $node is, access
   *   is checked for that object's revision.
   * @param \Drupal\node\NodeInterface $payment
   *   (optional) A node object. Used for checking access to a node's default
   *   revision when $node_revision is unspecified. Ignored when $node_revision
   *   is specified. If neither $node_revision nor $node are specified, then
   *   access is denied.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, AccountInterface $account, PaymentMethodConfigurationInterface $payment_method_configuration = NULL, $external_status = '') {
    $payment_method = $this->paymentMethodStorage->load($payment_method_configuration->getPluginId());
    return TRUE;
//    return AccessResult::allowedIf($account->id() == $payment->getOwnerId()
//      && $payment->getPaymentMethod()->getPluginDefinition()['provider'] == 'robokassa_payment'
//      && $payment->getPaymentStatus()->getPluginId() == 'payment_pending');
  }

}
