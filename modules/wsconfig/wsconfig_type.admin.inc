<?php

/**
 * @file
 * WsConfig type editing UI
 *
 * @author Mathew Winstone <mwinstone@coldfrontlabs.ca>
 * @copyright 2011 Coldfront Labs Inc.
 * @license Copyright (c) 2011 All rights reserved
 */

/**
 * Generate the wsconfig type editing form
 */
function wsconfig_type_form($form, &$form_state, $wsconfig_type, $op = 'edit') {
    if ($op == 'clone') {
      $wsconfig_type->label .= ' (cloned)';
      $wsconfig_type->type = '';
    }

    $form['label'] = array(
      '#title' => t('Label'),
      '#type' => 'textfield',
      '#default_value' => $wsconfig_type->label,
      '#description' => t('The human-readable name of this wsconfig type.'),
      '#required' => TRUE,
      '#size' => 30,
    );
    // Machine-readable type name.
    $form['type'] = array(
      '#type' => 'machine_name',
      '#default_value' => isset($wsconfig_type->type) ? $wsconfig_type->type : '',
      '#maxlength' => 32,
  //    '#disabled' => $wsconfig_type->isLocked() && $op != 'clone',
      '#machine_name' => array(
        'exists' => 'wsconfig_get_types',
        'source' => array('label'),
      ),
      '#description' => t('A unique machine-readable name for this wsconfig type. It must only contain lowercase letters, numbers, and underscores.'),
    );

    $form['data']['#tree'] = TRUE;
    $form['data']['endpoint'] = array(
      '#type' => 'textfield',
      '#title' => t('Web service endpoint'),
      '#description' => t('Full URL to the service endpoint Ex: http://example.com/rest'),
      '#default_value' => !empty($wsconfig_type->data['endpoint']) ? $wsconfig_type->data['endpoint'] : '',
    );

    $connectors = variable_get('wsconfig_connectors');
    $connectors_options = array();
    foreach ($connectors as $connector) {
      $connectors_options[$connector['class']] = t($connector['name']);
    }

    $form['data']['connector'] = array(
      '#type' => 'select',
      '#title' => t('Service Connector'),
      '#description' => t('Connector to use to make the web service requests.'),
      '#options' => $connectors_options,
      '#default_value' => !empty($wsconfig_type->data['connector']) ? $wsconfig_type->data['connector'] : '',
    );

    // @todo add token support
    // @todo list available tokens
    
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save Web Service Configuration type'),
      '#weight' => 40,
    );

    //Locking not supported yet
    /*if (!$wsconfig_type->isLocked() && $op != 'add') {
      $form['actions']['delete'] = array(
        '#type' => 'submit',
        '#value' => t('Delete wsconfig type'),
        '#weight' => 45,
        '#limit_validation_errors' => array(),
        '#submit' => array('wsconfig_type_form_submit_delete')
      );
    }*/
    return $form;
}

/**
 * Form API submit callback for the type form.
 */
function wsconfig_type_form_submit(&$form, &$form_state) {
  $wsconfig_type = entity_ui_form_submit_build_entity($form, $form_state);
  $wsconfig_type->save();
  $form_state['redirect'] = 'admin/structure/wsconfig_types';
}

/**
 * Form API submit callback for the delete button.
 */
function wsconfig_type_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/structure/wsconfig_types/manage/' . $form_state['wsconfig_type']->type . '/delete';
}
