<?php
/**
 * Contains \Drupal\payment_offsite_api\Plugin\Payment\MethodConfiguration\PaymentMethodBaseOffsite.
 */

namespace Drupal\payment_offsite_api\Plugin\Payment\MethodConfiguration;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBase;
use Drupal\plugin\PluginType\PluginTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorManagerInterface;


class PaymentMethodConfigurationBaseOffsite extends PaymentMethodConfigurationBase implements ContainerFactoryPluginInterface {
  /**
   * The payment status plugin type.
   *
   * @var \Drupal\plugin\PluginType\PluginTypeInterface
   */
  protected $paymentStatusType;

  /**
   * The plugin selector manager.
   *
   * @var \Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorManagerInterface
   */
  protected $pluginSelectorManager;

  /**
   * Constructs a new instance.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed[] $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorManagerInterface
   *   The plugin selector manager.
   * @param \Drupal\plugin\PluginType\PluginTypeInterface $payment_status_type
   *   The payment status plugin type.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, TranslationInterface $string_translation, ModuleHandlerInterface $module_handler, PluginSelectorManagerInterface $plugin_selector_manager, PluginTypeInterface $payment_status_type) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition, $string_translation, $module_handler);
    $this->paymentStatusType = $payment_status_type;
    $this->pluginSelectorManager = $plugin_selector_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\plugin\PluginType\PluginTypeManagerInterface $plugin_type_manager */
    $plugin_type_manager = $container->get('plugin.plugin_type_manager');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('string_translation'),
      $container->get('module_handler'),
      $container->get('plugin.manager.plugin.plugin_selector'),
      $plugin_type_manager->getPluginType('payment_status')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['plugin_form'] = array(
      '#process' => array(array($this, 'processBuildConfigurationForm')),
      '#type' => 'container',
    );

    return $form;
  }

  /**
   * Gets all status to set on payment execution.
   */
  public function getStatuses() {
    return $this->configuration['ipnStatuses'];
  }

  /**
   * Gets the status to set on payment execution.
   *
   * @return string
   *   The plugin ID of the payment status to set.
   */
  public function getStatusId($status_id) {
    return $this->configuration['ipnStatuses'][$status_id];
  }

  /**
   * Sets the status to set on payment execution.
   *
   * @param string $status
   *   The plugin ID of the payment status to set.
   *
   * @return $this
   */
  public function setStatusId($status_id, $status) {
    $this->configuration['ipnStatuses'][$status_id] = $status;

    return $this;
  }

  /**
   * Implements a form API #process callback.
   */
  public function processBuildConfigurationForm(array &$element, FormStateInterface $form_state, array &$form) {
    $workflow_group = implode('][', array_merge($element['#parents'], array('workflow')));
    $element['workflow'] = array(
      '#type' => 'vertical_tabs',
    );
    $element['statuses'] = array(
      '#group' => $workflow_group,
      '#open' => TRUE,
      '#type' => 'details',
      '#title' => $this->t('Provider statuses mapping'),
    );

    foreach (array_keys($this->getStatuses()) as $status) {
      $element['statuses'][$status . '_status'] = $this->getSinglePaymentStatusSelector($form_state, $status)
        ->buildSelectorForm([], $form_state);
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    foreach (array_keys($this->getStatuses()) as $status) {
      $this->getSinglePaymentStatusSelector($form_state, $status)
        ->validateSelectorForm($form['plugin_form']['statuses'][$status . '_status'], $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    foreach (array_keys($this->getStatuses()) as $status) {
      $this->getSinglePaymentStatusSelector($form_state, $status)
        ->submitSelectorForm($form['plugin_form']['statuses'][$status . '_status'], $form_state);
      $this->setStatusId($status, $this->getSinglePaymentStatusSelector($form_state, $status)
        ->getSelectedPlugin()
        ->getPluginId());
    }
  }

  /**
   * Gets the payment status selector for the execute phase.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorInterface
   */
  protected function getSinglePaymentStatusSelector(FormStateInterface $form_state, $status) {
    $plugin_selector = $this->getPaymentStatusSelector($form_state, $status, $this->getStatusId($status))
      ->setLabel($this->t('Payment @status status', ['@status' => Unicode::ucfirst($status)]));

    return $plugin_selector;
  }

  /**
   * Gets the payment status selector.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param string $type
   * @param string $default_plugin_id
   *
   * @return \Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorInterface
   */
  protected function getPaymentStatusSelector(FormStateInterface $form_state, $type, $default_plugin_id) {
    $key = 'payment_status_selector_' . $type;
    if ($form_state->has($key)) {
      $plugin_selector = $form_state->get($key);
    }
    else {
      $plugin_selector = $this->pluginSelectorManager->createInstance('payment_select_list');
      $plugin_selector->setSelectablePluginType($this->paymentStatusType);
      $plugin_selector->setRequired(TRUE);
      $plugin_selector->setCollectPluginConfiguration(FALSE);
      $plugin_selector->setSelectedPlugin($this->paymentStatusType->getPluginManager()
        ->createInstance($default_plugin_id));

      $form_state->set($key, $plugin_selector);
    }

    return $plugin_selector;
  }
}
