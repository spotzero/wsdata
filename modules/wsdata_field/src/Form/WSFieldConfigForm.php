<?php

namespace Drupal\wsdata_field\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field_ui\FieldUI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WSFieldsConfigForm.
 *
 * @package Drupal\wsdata_field\Form
 */
class WSFieldConfigForm extends EntityForm {

  protected $entity;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('plugin.manager.wsfieldconfig')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wsfield_config_add_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param string $field_config
   *   The ID of the field config whose field storage config is being edited.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $field_config = NULL) {
    if ($field_config) {
      $field = FieldConfig::load($field_config);
      $form_state->set('field_config', $field);
      $form_state->set('entity_type_id', $field->getTargetEntityTypeId());
      $form_state->set('bundle', $field->getTargetBundle());
    }
    return parent::buildForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);


     // Load the field configurations.
    $field_config = $form_state->get('field_config');
    if (entity_load('wsfield_config', $field_config->get('field_name')) == NULL) {
      $wsfield_config_entity = $this->entity;
    }
    else {
      $this->entity = entity_load('wsfield_config', $field_config->get('field_name'));
      $wsfield_config_entity = $this->entity;
    }

    // Set the title.
    $form['#title'] = t('Web service field settings');

    // Set the ID as the field name.
    $form['id'] = [
      '#type' => 'hidden',
      '#value' => $field_config->get('field_name'),
    ];

    // Load the wscall entities.
    $wscalls = entity_load_multiple('wscall');
    $options = [];
    foreach ($wscalls as $wscall) {
      $options[$wscall->id()] = $wscall->label();
    }

    $form['wscall'] = [
      '#type' => 'select',
      '#title' => t('Web Service Call'),
      '#options' => $options,
      '#required' => TRUE,
      '#default_value' => $wsfield_config_entity->wscall,
      '#ajax' => [
        'callback' => '::wsfieldreplacementForm',
        'wrapper' => 'wscall-replacement-tokens-wrapper',
      ],
    ];

    // Fetch the replacement tokens for this wscall.
    $form['replacements'] = [
      '#id' => 'wscall-replacement-tokens-wrapper',
      '#type' => 'container',
    ];
    // Ajax callback to get the replacements tokens.
    if (!empty($wsfield_config_entity->wscall)) {
      foreach ($wscalls[$wsfield_config_entity->wscall]->getReplacements() as $replacement) {
        $form['replacements'][$replacement] = [
          '#type' => 'textfield',
          '#title' => $replacement,
          '#default_value' => isset($wsfield_config_entity->replacements[$replacement]) ? $wsfield_config_entity->replacements[$replacement] : '',
        ];
      }
    }

    $form['data'] = [
      '#type' => 'textarea',
      '#title' => t('Data'),
    ];

    $form['returnToken'] = array(
      '#type' => 'textfield',
      '#title' => t('Token to select'),
      '#default_value' => $wsfield_config_entity->returnToken,
      '#description' => t('Seperate element names with a ":" to select nested elements.'),
    );
    return $form;
  }

  /**
   * Ajax Callback.
   * This is still not working ?
   */
  public function wsfieldreplacementForm(array $form, FormStateInterface $form_state) {
    return $form['replacements'];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $wscall_entity = entity_load('wscall', $form_state->getValue('wscall'));

    $replacements = [];
    foreach ($wscall_entity->getReplacements() as $replacement) {
      $replacements[$replacement] = $form_state->getValue($replacement);
    }

    $wsfieldconfig_entity = $this->entity;
    $wsfieldconfig_entity->replacements = $replacements;
    $status = $wsfieldconfig_entity->save();

    // Set the redirect to the next destination in the steps.
    $request = $this->getRequest();
    if (($destinations = $request->query->get('destinations')) && $next_destination = FieldUI::getNextDestination($destinations)) {
      $request->query->remove('destinations');
      $form_state->setRedirectUrl($next_destination);
    }
    else {
      // if no redirect is set go to the entity type and bundle field UI page.
      $field_config = $form_state->get('field_config');
      $form_state->setRedirectUrl(FieldUI::getOverviewRouteInfo($field_config->getTargetEntityTypeId(), $field_config->getTargetBundle()));
    }
  }
}
