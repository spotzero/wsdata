<?php

namespace Drupal\wsdata\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\State;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Admin form for the WSData module.
 */
class WSDataAdminForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler,
    State $state
  ) {
    $this->setConfigFactory($config_factory);
    $this->module_handler = $module_handler;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wsdata_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'wsdata_admin.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $disable = TRUE;

    // Only allow this if the devel module is enabled.
    if ($this->module_handler->moduleExists('kint')) {
      $disable = FALSE;
    }
    else {
      $this->state->set('wsdata_debug_mode', 0);
    }

    $form['debug_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Debug Mode'),
      '#description' => $this->t('Devel and Kink modules must be installed and enabled for this functionality to work.'),
      '#disabled' => $disable,
      '#default_value' => $this->state->get('wsdata_debug_mode'),
    ];

    $form['performance_log'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Performance log'),
      '#description' => $this->t('Log WSData performace to watchdog'),
      '#default_value' => $this->state->get('wsdata_performance_log', 0),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->state->set('wsdata_debug_mode', $values['debug_mode']);
    $this->state->set('wsdata_performance_log', $values['performance_log']);
    parent::submitForm($form, $form_state);
  }

}
