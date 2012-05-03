<?php

/**
 * @file
 * Defines core functionality for web service powered fields
 *
 * @author Mathew Winstone <mwinstone@coldfrontlabs.ca>
 * @author David Pascoe-Deslauriers <dpascoed@coldfrontlabs.ca>
 * @copyright 2011 Coldfront Labs Inc.
 * @license Copyright (c) 2011 All rights reserved
 */

/**
 *  Field configuration Form
 */
function wsfields_field_settings_form($form, &$form_state, $instance) {
  drupal_set_title($instance['label']);

  $wsenabled = FALSE;
  $wsconfigset = NULL;
  $wsremotename = "";
  $wsprocessor = "";
  $wsproperty = "";

  $instance['entity_type'];
  $instance['bundle'];

  if (isset($instance['settings']['wsfield'])) {
    $wsenabled = $instance['settings']['wsfield'];
  }

  if (isset($instance['settings']['wsfield_configset'])) {
    $wsconfigset = $instance['settings']['wsfield_configset'];
  }

  if (isset($instance['settings']['wsfield_remotename'])) {
    $wsremotename = $instance['settings']['wsfield_remotename'];
  }

  if (isset($instance['settings']['wsfield_processor'])) {
    $wsprocessor = $instance['settings']['wsfield_processor'];
  }

  if (isset($instance['settings']['wsfield_property'])) {
    $wsproperty = $instance['settings']['wsfield_property'];
  }

  $bundle = $instance['bundle'];
  $entity_type = $instance['entity_type'];
  $field_name = $instance['field_name'];

  $field = field_info_field($field_name);
  $field_type = field_info_field_types($field['type']);
  $widget_type = field_info_widget_types($instance['widget']['type']);
  $bundles = field_info_bundles();
  $bundle_label = $bundles[$entity_type][$bundle]['label'];

  $form = array(
    '#bundle' => $bundle,
    '#entity_type' => $entity_type,
    '#field_name' => $field_name,
  );

  $form['wsfields'] = array(
    '#type' => 'fieldset',
    '#title' => t('Web Service Settings'),
  );
  $form['wsfields']['wsfield_enabled'] = array(
    '#title' => t("Enable Web Service for this field"),
    '#type' => 'checkbox',
    '#default_value' =>  isset($form_state['values']['wsfields']['enabled']) ? $form_state['values']['wsfields']['enabled'] : $wsenabled,
    '#description' => t('Select whether the data from this field should be queried from a web service.'),
  );

  $form['wsfields']['wsfield_configset'] = array (
    '#type' => 'select',
    '#title' => t('Web Service Method'),
    '#options' => wsconfig_get_list(array()),
    '#default_value' => isset($form_state['values']['wsfield_configset']) ? $form_state['values']['wsfield_configset'] : $wsconfigset,
    '#description' => t('Choose the Web Service Method used for this service'),
    '#states' => array(
      'invisible' => array(
        'input[name="wsfield_enabled"]' => array('checked' => FALSE),
      ),
    ),
  );

  $form['wsfields']['wsfield_remotename'] = array (
    '#type' => 'textfield',
    '#title' => t('Web Service Remote Data Name'),
    '#default_value' => isset($form_state['values']['wsfield_remotename']) ? $form_state['values']['wsfield_remotename'] : $wsremotename,
    '#description' => t('The name of field in the data return by the remote service.  Notes: Leave this field blank to select all of the data returned.  Seperate element names with a ":" to select nested elements.'),
    '#states' => array(
      'invisible' => array(
        'input[name="wsfield_enabled"]' => array('checked' => FALSE),
      ),
    ),
  );
  
  $form['wsfields']['wsfield_processor'] = array(
    '#type' => 'select',
    '#title' => t('Processor'),
    '#multiple' => FALSE,
    '#description' => t('Select a data processor.'),
    '#options' => wsconfig_get_field_processors(array($field_type['property_type'])),
    '#default_value' => isset($form_state['values']['wsfields']['wsfield_processor']) ? $form_state['values']['wsfields']['wsfield_processor'] : $wsprocessor,
    '#states' => array(
      'invisible' => array(
        'input[name="wsfield_enabled"]' => array('checked' => FALSE),
      ),
    ),
  );

  $properties = entity_metadata_wrapper('user')->getPropertyInfo();
  foreach ($properties as $pname => $property) {
    if (!isset($property['field'])) {
      $properties_options[$pname] = $pname . '    (' . $property['label'] . ')';
    }
  }

  $form['wsfields']['wsfield_property'] = array(
    '#type' => 'select',
    '#multiple' => TRUE,
    '#size' => sizeof($properties_options),
    '#options' => $properties_options,
    '#title' => t('Remote Key'),
    '#description' => t('Entity property that contains the remote id of the remote object.'),
    '#default_value' => isset($form_state['input']['wsfield_property']) ? $form_state['input']['wsfield_property'] : $wsproperty,
    '#states' => array(
      'invisible' => array(
        'input[name="wsfield_enabled"]' => array('checked' => FALSE),
      ),
    ),
  );

  $tokens = wsconfig_get_list_tokens();
  foreach($tokens as $id => $toks) {
    $form['wsfields']['wsfield_token_list'][$id] = array(
      '#type' => 'container',
      '#children' => t('Available Tokens: ') . implode(", ", $toks),
      '#states' => array(
        'invisible' => array(
          ':input[name="wsfield_configset"]' => array('value' => $id),
        ),
      ),
    );
  }


  $form['wsfields']['wsfield_property_weight'] = array();
  foreach($wsproperty as $property) {
    $form['wsfields']['wsfield_property_weight'][$property] = array(
      '#title' => $property,
      '#type' => 'textfield',
      '#default_value' => '',
      '#description' => t('Token this key should replace in the method call.'),
      '#states' => array(
        'invisible' => array(
          'input[name="wsfield_enabled"]' => array('checked' => FALSE),
        ),
      ),
    );
  }



  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save'));

//  $form['#validate'] = array();
  $form['#submit'] = array('wsfields_field_settings_form_submit');

  return $form;
}

/**
 * Submit function for wsfield_field_settings_form().
 */
function wsfields_field_settings_form_submit($form, &$form_state) {
  $form_values = $form_state['values'];
  $bundle = $form['#bundle'];
  $entity_type = $form['#entity_type'];
  $field_name = $form['#field_name'];

  // Retrieve the stored instance settings to merge with the incoming values.
  $instance = field_read_instance($entity_type, $field_name, $bundle);
  if (isset($form_state['values']['wsfield_enabled']) and isset($form_state['values']['wsfield_configset']) and $form_state['values']['wsfield_enabled']) {
    $instance['settings']['wsfield'] = TRUE;
    $instance['settings']['wsfield_configset'] = check_plain($form_state['values']['wsfield_configset']);
    $instance['settings']['wsfield_remotename'] = check_plain($form_state['values']['wsfield_remotename']);
    $instance['settings']['wsfield_processor'] = check_plain($form_state['values']['wsfield_processor']);
    $instance['settings']['wsfield_property'] = $form_state['values']['wsfield_property'];
  } else {
    unset( $instance['settings']['wsfield']);
  }
  field_update_instance($instance);
  
  drupal_set_message(t('Web service settings saved.'));
}

