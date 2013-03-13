<?php

/**
 * @file
 * Defines the wsfields storage forms
 */

/**
 *  Wsfields settings form
 */
function wsfields_storage_settings_form($form, &$form_state) {
  /* Loads initial data */

  $fields = field_info_fields();
  $rows = array();
  $processors = wsconfig_get_field_processors();
  $fieldtypes = field_info_field_types();

  $query = new EntityFieldQuery();
  $wsconfigs = $query->entityCondition('entity_type', 'wsconfig')->execute();
  $wsconfigs = wsconfig_load_multiple(array_keys($wsconfigs['wsconfig']));
  foreach ($wsconfigs as $key => $wsconfig) {
    $wsconfigs[$wsconfig->name] = $wsconfig;
    $wsconfigs[$wsconfig->name]->path = entity_uri('wsconfig', $wsconfig);
    unset($wsconfigs[$key]);
  }

  $fieldtypeoptions = array();
  foreach ($fieldtypes as $key => $type) {
    $fieldtypeoptions[$key] = $type['label'];
  }
  asort($fieldtypeoptions);

  /* Build the Table */

  $header = array();
  $header[] = array('data' => t('Field'), 'field' => 'field');
  $header[] = array('data' => t('Type'), 'field' => 'type');
  $header[] = array('data' => t('Processor'), 'field' => 'processor');
  $header[] = array('data' => t('WSConfig'), 'field' => 'wsconfig');
  $header[] = array('data' => t('Operations'));

  foreach ($fields as $field) {
    if ($field['storage']['type'] != 'wsfields_storage') {
      continue;
    }
    $row = array();
    $row['data']['field_name'] = $field['field_name'];
    $row['data']['type'] = $fieldtypes[$field['type']]['label'];

    if (isset($processors[$field['storage']['settings']['processor']])) {
      $row['data']['processor'] = $processors[$field['storage']['settings']['processor']];
    } else {
      $row['data']['processor'] = $field['storage']['settings']['processor'];
    }
    if (isset($wsconfigs[$field['storage']['settings']['wsconfig_name']])) {
      $row['data']['wsconfig'] = l( $wsconfigs[$field['storage']['settings']['wsconfig_name']]->title, $wsconfigs[$field['storage']['settings']['wsconfig_name']]->path['path']);
    } else {
      $row['data']['wsconfig'] = '';
    }

    $operations = array();
    $operations['edit'] = array(
      'title' => t('edit'),
      'href' => "admin/config/services/wsfields_storage/edit",
      'query' => array('field' => $field['field_name']),
    );
    $operations['delete'] = array(
      'title' => t('delete'),
      'href' => "admin/config/services/wsfields_storage/delete",
      'query' => array('field' => $field['field_name']),
    );
    $row['data']['operations'] = array(
      'data' => array(
        '#theme' => 'links',
        '#links' => $operations,
        '#attributes' => array('class' => array('links', 'inline', 'nowrap')),
      ),
    );

    $rows[] = $row;
  }

  $form['wsfields_storage_table'] = array(
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
  );

  /* Add a new field */
  $form['wsfields_storage_add_field'] = array(
    '#title' => t('Add a WSFields Storage Field'),
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    'wsfields_storage_field_name' => array(
      '#type' => 'machine_name',
      '#title' => t('Field machine name'),
      '#machine_name' => array(
        'exists' => '_wsfields_storage_field_name_exists',
      ),
    ),
    'wsfields_storage_field_type' => array(
      '#type' => 'select',
      '#title' => t('Type'),
      '#multiple' => FALSE,
      '#description' => t('Field Type'),
      '#options' => $fieldtypeoptions,
    ),
    'submit' => array(
      '#type' => 'submit',
      '#value' => t('Save'),
    ),
  );

  return $form;
}

function wsfields_storage_settings_form_submit($form, &$form_state) {
  $field_name = $form_state['values']['wsfields_storage_field_name'];
  $field_type = $form_state['values']['wsfields_storage_field_type'];
  
  $field = array(
    'field_name' => $field_name,
    'translatable' => 0,
    'type' => $field_type,
    'storage' => array(
      'active' => '1',
      'module' => 'wsfields_storage',
      'settings' => array(),
      'type' => 'wsfields_storage',
    ),
  );
  field_create_field($field);
}

function _wsfields_storage_field_name_exists($value) {
  return (bool) field_read_fields(array('field_name' => $value), array('include_inactive' => TRUE));
}

function wsfields_storage_edit_field_form($form, &$form_state) {
  if (!isset($_GET['field'])) {
    return $form;
  }

  $field = $_GET['field'];
  $fields = field_info_fields();
  if (!isset($fields[$field])) {
    return $form;
  }
  $field = $fields[$field];

  dpm($field);

  drupal_set_title(t('WSField Storage settings for @field', array('@field' => $field['field_name'])));

  $wsconfigs = wsconfig_get_list_by_name(array());

  $wsconfig_name =
    isset($form_state['values']['wsconfig_name']) ? $form_state['values']['wsconfig_name'] :
      isset($field['storage']['settings']['wsconfig_name']) ? $field['storage']['settings']['wsconfig_name'] : '';
  $processor =
    isset($form_state['values']['processor']) ? $form_state['values']['processor'] :
      isset($field['storage']['settings']['processor']) ? $field['storage']['settings']['processor'] : '';


  $form['processor'] = array(
    '#type' => 'select',
    '#title' => t('Processor'),
    '#multiple' => FALSE,
    '#description' => t('Select a data processor.'),
    '#options' => wsconfig_get_field_processors(),
    '#default_value' => $processor,
  );

  $form['remotename'] = array (
    '#type' => 'textfield',
    '#title' => t('Web Service Remote Data Name'),
    '#default_value' =>
      isset($form_state['values']['remotename']) ? $form_state['values']['remotename'] :
        isset($field['storage']['settings']['remotekey']) ? $field['storage']['settings']['remotekey'] : '',
    '#description' => t('The name of field in the data return by the remote service.  Notes: Leave this field blank to select all of the data returned.  Seperate element names with a ":" to select nested elements.'),
  );

  // If Field Translation isn't available, don't allow languages for this field

  $form['translation'] = array(
    '#title' => t("Translation Available"),
    '#type' => 'checkbox',
    '#description' => 'Whether or not the service provides translated data.'
  );

  if (!module_exists('locale') or !module_exists('entity_translation')) {
    $field['storage']['settings']['translation'] = FALSE;
    $form['translation']['#disabled'] = TRUE;
    $form['translation']['#default_value'] = FALSE;
    $form['translation']['#description'] = t('The Locale and Entity Translation modules must be enabled for translated fields.');
  } elseif (!$field['translatable']) {
    $field['storage']['settings']['translation'] = FALSE;
    $form['translation']['#disabled'] = TRUE;
    $form['translation']['#default_value'] = FALSE;
    $form['translation']['#description'] = t('This field is not translatable.');
  } else {
    $form['translation']['#default_value'] =
      isset($form_state['values']['translation']) ? $form_state['values']['translation'] : 
        isset($field['storage']['settings']['translation'])? $field['storage']['settings']['translation']: FALSE;
    $form['translation']['#disabled'] = FALSE;
  }

  $form['wsconfig_name'] = array(
    '#type' => 'select',
    '#title' => t('WSConfig'),
    '#options' => $wsconfigs,
    '#default_value' => $wsconfig_name,
    '#description' => t('Choose the Web Service Method used for this service'),
  );

  foreach(wsconfig_get_list_tokens() as $id => $tokens) {
    $form['wsfield_token_list'][$id] = array(
      '#title' => $wsconfigs[$id],
      '#type' => 'fieldset',
      '#description' => t('Available web service calls.'),
      '#states' => array(
        'visible' => array(
          ':input[name="wsconfig_name"]' => array('value' => $id),
        ),
      ),
    );

    foreach ($tokens as $call => $calltokens) {
      $form['wsfield_token_list'][$id][$call] = array(
        '#title' => t($call),
        '#type' => 'fieldset',
        '#description' => t('Tokens available for replacement.'),
      );

      foreach ($calltokens as $token) {
        $formfieldname = "$id-$call-$token";
        $form['wsfield_token_list'][$id][$call][$formfieldname] = array(
          '#title' => $token,
          '#type' => 'textfield',
          '#desciption' => t('The entity propriety to replace this token with.'),
          '#default_value' =>
            isset($form_state['values'][$formfieldname]) ? $form_state['values'][$formfieldname] :
              isset($field['storage']['settings']['propertymap'][$call][$token]) ? $field['storage']['settings']['propertymap'][$call][$token] : '',
        );
      }
    }
  }

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save'));
  $form['actions']['cancel'] = array('#type' => 'item', '#markup' => l(t('Cancel'), 'admin/config/services/wsfields_storage'));

  return $form;
}

function wsfields_storage_edit_field_form_validate($form, &$form_state) {

}

function wsfields_storage_edit_field_form_submit($form, &$form_state) {

}

function wsfields_storage_delete_field_form($form, &$form_state) {
  if (!isset($_GET['field'])) {
    return $form;
  }

  $field = $_GET['field'];
  $question = t('Are you sure you want to delete field @field?', array('@field' => $field));
  $path = 'admin/config/services/wsfields_storage';
  $description = t('This action cannot be undone.');
  $yes = t('Delete');
  $no = t('Cancel');
 
  $form = confirm_form($form, $question, $path, $description, $yes, $no);
  return $form;
}

function wsfields_storage_delete_field_form_submit($form, &$form_state) {
  if (!isset($_GET['field'])) {
    return;
  }

  $field = $_GET['field'];

  $fields = field_info_fields();
  if (isset($fields[$field])) {
    field_delete_field($field);
    drupal_set_message(t('Field @field has been deleted.', array('@field' => $field)));
  }
  drupal_goto('admin/config/services/wsfields_storage');
}
