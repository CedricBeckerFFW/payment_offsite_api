<?php
/**
 * Created by PhpStorm.
 * User: niko
 * Date: 12.03.16
 * Time: 12:43
 */

namespace Drupal\payment_offsite_api\Routing;


use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class PaymentOffsiteRoutes {

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $route_collection = new RouteCollection();
    $external_statuses = \Drupal::service('plugin.manager.payment.external_status');

    $route = new Route(
    // Path to attach this route to:
      '/example/{}',
      // Route defaults:
      array(
        '_controller' => '\Drupal\payment_offsite_api\Controller\PaymentOffsiteController::content',
        '_title' => 'Hello',
      ),
      // Route requirements:
      array(
        '_permission'  => 'access content',
      )
    );

    // Add the route under the name 'example.content'.
    $route_collection->add('example.content', $route);
    return $route_collection;
  }

}
