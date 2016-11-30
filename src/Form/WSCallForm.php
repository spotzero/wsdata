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
    $options = array();
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
