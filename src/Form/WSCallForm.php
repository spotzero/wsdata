<?php

namespace Drupal\wsdata\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class WSCallForm.
 *
 * @package Drupal\wsdata\Form
 */
class WSCallForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $wscall_entity = $this->entity;

    if (isset($wscall_entity->needSave) and $wscall_entity->needSave) {
      drupal_set_message($this->t('You have unsaved changes.  Click save to save this entity.'), 'warning');
    }

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $wscall_entity->label(),
      '#description' => $this->t("Label for the Web Service Call."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $wscall_entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\wsdata\Entity\WSCall::load',
      ],
      '#disabled' => !$wscall_entity->isNew(),
    ];

    $servers = entity_load_multiple('wsserver');
    $options = [];
    foreach ($servers as $server) {
      $options[$server->id()] = $server->label();
    }

    $form['wsserver'] = [
      '#type' => 'select',
      '#title' => $this->t('Web Service Server'),
      '#description' => $this->t('Data source.'),
      '#options' => $options,
      '#required' => TRUE,
      '#default_value' => $wscall_entity->wsserver,
    ];

    $triggering = $form_state->getTriggeringElement();

    if ($triggering['#id'] == 'wscall_new_method') {
      $values = $form_state->getValues();
      $wscall_entity->setMethod($values['add_method'], $values['new_method_name'], $values['new_method_path']);
    }

    // Add new method form.
    $form['new_methods'] = [
      '#id' => 'wscall_new_method',
      '#type' => 'fieldset',
      '#title' => $this->t('Add new method'),
    ];

    $form['new_methods'] += $this->addMethodForm($wscall_entity, $form_state);

    // List methods Table
    // @todo: add

    $form['methods'] = [
      '#id' => 'wscall_edit_method',
      '#type' => 'fieldset',
      '#title' => $this->t('Edit methods'),
    ];

    $form['methods'] += $this->editMethodsForm($wscall_entity, $form_state);

    return $form;
  }


  public function addMethodForm($wscall_entity, FormStateInterface $form_state) {
    $form = [];
    $possible =  $wscall_entity->getPossibleMethods();
    
    $form['add_method'] = [
      '#type' => 'select',
      '#title' => t('Method type'),
      '#options' => $possible,
      '#required' => TRUE,
    ];

    $form['new_method_name'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#description' => $this->t('If multiple methods of this type are allowed, this machine name is would it will be accessed.'),
    ];

    $form['new_method_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Method endpoint'),
      '#description' => $this->t('Resulting endpoint would be: %end<em>/Method endpoint</em>', ['%end' => $wscall_entity->getEndpoint()]),
    ];

    $form['add_method_submit'] = [
      '#id' => 'wscall_new_method',
      '#type' => 'button',
      '#value' => $this->t('Add method'),
    ];
    return $form;
  }

  public function editMethodsForm($wscall_entity, FormStateInterface $form_state) {
    $form = [];
    $methods = $wscall_entity->getMethods();

    if (isset($methods['single'])) {
      $form['single'] = array(
        '#title' => $this->t('Limited Methods'),
        '#decription' => $this->t('Only one instance of these methods is allowed per call'),
        '#type' => 'fieldset',
      );
      foreach ($methods['single'] as $type => $method) {
      }
    }

    if (isset($methods['multiple'])) {
      foreach ($methods['multiple'] as $type => $types) {
        foreach ($types as $name => $method) {

        }
      }
    }
    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $wscall_entity = $this->entity;

    $status = $wscall_entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Web Service Call.', [
          '%label' => $wscall_entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Web Service Call.', [
          '%label' => $wscall_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($wscall_entity->urlInfo('collection'));
  }

}
