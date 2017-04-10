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

    $form['options'] = $wscall_entity->getOptionsForm($options);

    $options = $wscall_entity->getOptions();
    foreach ($options as $name => $option) {
      if (isset($form['options'][$name])) {
        $form['options'][$name]['#default_value'] = $option;
      }
    }

    $decoders = \Drupal::service('plugin.manager.wsdecoder');
    $decoder_definitions = $decoders->getDefinitions();
    $options = array('' => $this->t('None'));
    foreach ($decoder_definitions as $key => $decoder) {
      $options[$key] = $decoder['label']->render();
    }

    $form['wsdecoder'] = [
      '#type' => 'select',
      '#title' => $this->t('Decoder'),
      '#description' => $this->t('Decoder to decode the result.'),
      '#options' => $options,
      '#required' => TRUE,
      '#default_value' => $wscall_entity->wsdecoder,
    ];

    $encoders = \Drupal::service('plugin.manager.wsencoder');
    $encoder_definitions = $encoders->getDefinitions();
    $options = array('' => $this->t('None'));
    foreach ($encoder_definitions as $key => $encoder) {
      $options[$key] = $encoder['label']->render();
    }

    $form['wsencoder'] = [
      '#type' => 'select',
      '#title' => $this->t('Encoder'),
      '#description' => $this->t('Encoder to encode the data sent to the web service.'),
      '#options' => $options,
      '#required' => TRUE,
      '#default_value' => $wscall_entity->wsencoder,
    ];

    if (!\Drupal::moduleHandler()->moduleExists('wsdata_extras')) {
      $form['wsdecoder']['#description'] .= '  ' . $this->t('Looking for more decoder plugins?  Try enabling the <em>wsdata_extras</em> module.');
      $form['wsencoder']['#description'] .= '  ' . $this->t('Looking for more encoder plugins?  Try enabling the <em>wsdata_extras</em> module.');
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $wscall_entity = $this->entity;
    $wscall_entity->setOptions($form_state->getValues());
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
