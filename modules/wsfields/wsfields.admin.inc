<?php

/**
 * @file
 * Defines core functionality for web service powered fields
 */

/**
 *  Field configuration Form
 */
function wsfields_field_settings_form($form, &$form_state, $instance) {
  drupal_set_title($instance['label']);

  $wsenabled = FALSE;
  $wstranslated = FALSE;
  $wsconfigset = NULL;
  $wsremotename = "";
  $wsprocessor = "";
  $wsproperty = "";
  $wspropertytoken = array();

  if (isset($instance['settings']['wsfield'])) {
    $wsenabled = $instance['settings']['wsfield'];
  }

  if (isset($instance['settings']['wsfield_translated'])) {
    $wstranslated = $instance['settings']['wsfield_translated'];
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

  if (isset($instance['settings']['wsfield_property_tokens'])) {
    $wspropertytoken = $instance['settings']['wsfield_property_tokens'];
  }

  $bundle = $instance['bundle'];
  $entity_type = $instance['entity_type'];
  $field_name = $instance['field_name'];

  $field = field_info_field($field_name);
//  $field_type = field_info_field_types($field['type']);
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

  $form['wsfields']['wsfield_translated'] = array(
    '#title' => t("Translation Available"),
    '#type' => 'checkbox',
    '#default_value' =>  isset($form_state['values']['wsfield_translated']['enabled']) ? $form_state['values']['wsfield_translated']['enabled'] : $wstranslated,
    '#description' => t('The Web Service returns language information for this field.'),
    '#states' => array(
      'invisible' => array(
        'input[name="wsfield_enabled"]' => array('checked' => FALSE),
      ),
    ),
  );

    // If Field Translation isn't available, don't allow languages for this field

  if (!module_exists('locale') or !module_exists('entity_translation')) {
    $wstranslated = FALSE;
    $form['wsfields']['wsfield_translated']['#disabled'] = TRUE;
    $form['wsfields']['wsfield_translated']['#default_value'] = FALSE;
    $form['wsfields']['wsfield_translated']['#description'] = t('The Locale and Entity Translation modules must be enabled for translated fields.');
  } else {
    $field_info = field_info_fields($instance['field_name']);

    if (!isset($field_info[$instance['field_name']]['translatable']) or !$field_info[$instance['field_name']]['translatable']) {
      $wstranslated = FALSE;
      $form['wsfields']['wsfield_translated']['#disabled'] = TRUE;
      $form['wsfields']['wsfield_translated']['#default_value'] = FALSE;
      $form['wsfields']['wsfield_translated']['#description'] = t('Translation is not enabled on this field.');
      $form['wsfields']['wsfield_translated']['#description'] .= '  ';
      $form['wsfields']['wsfield_translated']['#description'] .= l( t('Enable translation'), 'admin/config/regional/entity_translation/translatable/' . $instance['field_name']);
    }
  }

  $form['wsfields']['wsfield_configset'] = array(
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
//    '#options' => wsconfig_get_field_processors(array($field_type['property_type'])),
    '#options' => wsconfig_get_field_processors(array($field['type'])),
    '#default_value' => isset($form_state['values']['wsfields']['wsfield_processor']) ? $form_state['values']['wsfields']['wsfield_processor'] : $wsprocessor,
    '#states' => array(
      'invisible' => array(
        'input[name="wsfield_enabled"]' => array('checked' => FALSE),
      ),
    ),
  );

  $properties = entity_metadata_wrapper($entity_type)->getPropertyInfo();

  foreach($properties as $pname => $property) {
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
      '#type' => 'item',
      '#markup' => t('Available Tokens: ') . implode(", ", $toks),
      '#states' => array(
        'visible' => array(
          ':input[name="wsfield_configset"]' => array('value' => $id),
        ),
      ),
    );
  }


  $form['wsfields']['wsfield_property_token'] = array();
  foreach($wsproperty as $property) {
    $form['wsfields']['wsfield_property_token']['wsfield_property_token_' . $property] = array(
      '#title' => $property,
      '#type' => 'textfield',
      '#default_value' =>
        isset($form_state['values']['wsfields']['wsfield_property_token']['wsfield_property_token_' . $property]) ?
          $form_state['values']['wsfields']['wsfield_property_token']['wsfield_property_token_' . $property] :
          isset($wspropertytoken[$property])? $wspropertytoken[$property] : "",
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
  $bundle = $form['#bundle'];
  $entity_type = $form['#entity_type'];
  $field_name = $form['#field_name'];

  // Retrieve the stored instance settings to merge with the incoming values.
  $instance = field_read_instance($entity_type, $field_name, $bundle);
  if (isset($form_state['values']['wsfield_enabled']) and isset($form_state['values']['wsfield_configset']) and $form_state['values']['wsfield_enabled']) {
    $instance['settings']['wsfield'] = TRUE;
    $instance['settings']['wsfield_translated'] = $form_state['values']['wsfield_translated'];
    $instance['settings']['wsfield_configset'] = check_plain($form_state['values']['wsfield_configset']);
    $instance['settings']['wsfield_remotename'] = check_plain($form_state['values']['wsfield_remotename']);
    $instance['settings']['wsfield_processor'] = check_plain($form_state['values']['wsfield_processor']);
    $instance['settings']['wsfield_property'] = $form_state['values']['wsfield_property'];
    $instance['settings']['wsfield_property_tokens'] = array();
    $keys = array_keys($form_state['values']);
    foreach ($keys as $k) {
      if (strpos($k, 'wsfield_property_token_') !== FALSE) {
        $instance['settings']['wsfield_property_tokens'][drupal_substr($k,drupal_strlen('wsfield_property_token_'))] = trim($form_state['values'][$k]);
      }
    }
  } else {
    unset( $instance['settings']['wsfield']);
  }
  field_update_instance($instance);
  
  drupal_set_message(t('Web service settings saved.'));
}