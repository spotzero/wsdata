<?php

/**
 * @file
 * Admin forms for wsconfig
 */

/**
 * Form callback wrapper: create or edit a wsconfig.
 *
 * @param $wsconfig
 *   The wsconfig object being edited by this form.
 *
 * @see wsconfig_edit_form()
 */
function wsconfig_form_wrapper($wsconfig) {
  // Add the breadcrumb for the form's location.
  wsconfig_set_breadcrumb();
  return drupal_get_form('wsconfig_edit_form', $wsconfig);
}


/**
 * Form callback wrapper: delete a wsconfig.
 *
 * @param $wsconfig
 *   The wsconfig object being edited by this form.
 *
 * @see wsconfig_edit_form()
 */
function wsconfig_delete_form_wrapper($wsconfig) {
  // Add the breadcrumb for the form's location.
  //wsconfig_set_breadcrumb();
  return drupal_get_form('wsconfig_delete_form', $wsconfig);
}


/**
 * Form callback: create or edit a wsconfig.
 *
 * @param $wsconfig
 *   The wsconfig object to edit or for a create form an empty wsconfig object
 *     with only a wsconfig type defined.
 */
function wsconfig_edit_form($form, &$form_state, $wsconfig = NULL) {
  // Add the default field elements.
  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Web Service Configuration Title'),
    '#default_value' => isset($wsconfig->title) ? $wsconfig->title : '',
    '#maxlength' => 255,
    '#required' => TRUE,
    '#weight' => -5,    
  );
  
  $form['name'] = array(
    '#type' => 'machine_name',
    '#title' => t('Web Service Configuration Machine Name'),
    '#default_value' => isset($wsconfig->name) ? $wsconfig->name : '',
    '#maxlength' => 255,
    '#required' => TRUE,
    '#weight' => -4,
    '#machine_name' => array(
      'exists' => 'wsconfig_exists_by_name',
      'source' => array('title'),
    ),
  );
  
  $form['data']['create_data_method'] = array(
    '#type' => 'textfield',
    '#title' => t('Create Data Method Name'),
    '#description' => t('The method name for creating data. If keys are included in the method name, include them as tokens starting with a "%".  Example: user/%id/course'),
    '#size' => 40,
    '#maxlength' => 255,
    '#default_value' => !empty($wconfig->data['create_data_method']) ? $wsconfig->data['create_data_method'] : '',
  );
  $form['data']['read_data_method'] = array(
    '#type' => 'textfield',
    '#title' => t('Load Data Method Name'),
    '#description' => t('The method name for retrieving data. If keys are included in the method name, include them as tokens starting with a "%".  Example: user/%id/course'),
    '#size' => 40,
    '#maxlength' => 255,
    '#default_value' => !empty($wsconfig->data['read_data_method']) ? $wsconfig->data['read_data_method'] : '',
  );
  $form['data']['update_data_method'] = array(
    '#type' => 'textfield',
    '#title' => t('Update Data Method Name'),
    '#description' => t('The method name for updating data. If keys are included in the method name, include them as tokens starting with a "%".  Example: user/%id/course'),
    '#size' => 40,
    '#maxlength' => 255,
    '#default_value' => !empty($wsconfig->data['update_data_method']) ? $wsconfig->data['update_data_method'] : '',
  );
  $form['data']['delete_data_method'] = array(
    '#type' => 'textfield',
    '#title' => t('Delete Data Method Name'),
    '#description' => t('The method name for deleting data. If keys are included in the method name, include them as tokens starting with a "%".  Example: user/%id/course'),
    '#size' => 40,
    '#maxlength' => 255,
    '#default_value' => !empty($wsconfig->data['delete_data_method']) ? $wsconfig->data['delete_data_method'] : '',
  );
  
  // Add the field related form elements.
  $form_state['wsconfig'] = $wsconfig;
  field_attach_form('wsconfig', $wsconfig, $form, $form_state);

  $form['actions'] = array(
    '#type' => 'container',
    '#attributes' => array('class' => array('form-actions')),
    '#weight' => 400,
  );

  // We add the form's #submit array to this button along with the actual submit
  // handler to preserve any submit handlers added by a form callback_wrapper.
  $submit = array();

  if (!empty($form['#submit'])) {
    $submit += $form['#submit'];
  }

  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Web Service Configuration'),
    '#submit' => $submit + array('wsconfig_edit_form_submit'),
  );
  
  if (!empty($wsconfig->name)) {
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete Web Service Configuration'),
      '#suffix' => l(t('Cancel'), 'admin/content/wsconfig'),
      '#submit' => $submit + array('wsconfig_form_submit_delete'),
      '#weight' => 45,
    );
  }

  // We append the validate handler to #validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'][] = 'wsconfig_edit_form_validate';
  return $form;
}


/**
 * Form API validate callback for the wsconfig form
 */
function wsconfig_edit_form_validate(&$form, &$form_state) {
  $wsconfig = $form_state['wsconfig'];
  
  // Notify field widgets to validate their data.
  field_attach_form_validate('wsconfig', $wsconfig, $form, $form_state);
}


/**
 * Form API submit callback for the wsconfig form.
 * 
 * @todo remove hard-coded link
 */
function wsconfig_edit_form_submit(&$form, &$form_state) {
  
  $wsconfig = entity_ui_controller('wsconfig')->entityFormSubmitBuildEntity($form, $form_state);
  // Save the wsconfig and go back to the list of wsconfigs
  
  // Add in created and changed times.
  if ($wsconfig->is_new = isset($wsconfig->is_new) ? $wsconfig->is_new : 0){
    $wsconfig->created = time();
  }

  $wsconfig->changed = time();
  
  $wsconfig->save();
  $form_state['redirect'] = 'admin/content/wsconfig';
}

/**
 * Form API submit callback for the delete button.
 * 
 * @todo Remove hard-coded path
 */
function wsconfig_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/content/wsconfig/wsconfig/' . $form_state['wsconfig']->wsconfig_id . '/delete';
}


/**
 * Form callback: confirmation form for deleting a wsconfig.
 *
 * @param $wsconfig
 *   The wsconfig to delete
 *
 * @see confirm_form()
 */
function wsconfig_delete_form($form, &$form_state, $wsconfig) {
  $form_state['wsconfig'] = $wsconfig;

  $form['#submit'][] = 'wsconfig_delete_form_submit';

  $form = confirm_form($form,
    t('Are you sure you want to delete the Web Service Configuration %title?', array('%title' => $wsconfig->title)),
    'admin/content/wsconfig/wsconfig',
    '<p>' . t('This action cannot be undone.') . '</p>',
    t('Delete'),
    t('Cancel'),
    'confirm'
  );
  
  return $form;
}

/**
 * Submit callback for wsconfig_delete_form
 */
function wsconfig_delete_form_submit($form, &$form_state) {
  $wsconfig = $form_state['wsconfig'];

  wsconfig_delete($wsconfig);

  drupal_set_message(t('The Web Service Configuration %title has been deleted.', array('%title' => $wsconfig->title)));
  watchdog('wsconfig', 'Deleted web service configuration %name.', array('%name' => $wsconfig->name));

  $form_state['redirect'] = 'admin/content/wsconfig';
}

/**
 * Page to add Model Entities.
 *
 * @todo Pass this through a proper theme function
 */
function wsconfig_add_page() {
  $controller = entity_ui_controller('wsconfig');
  return $controller->addPage();
}

/**
 * Displays the list of available wsconfig types for wsconfig creation.
 *
 * @ingroup themeable
 */
function theme_wsconfig_add_list($variables) {
  $content = $variables['content'];
  $output = '';
  if ($content) {
    $output = '<dl class="wsconfig-type-list">';
    foreach ($content as $item) {
      $output .= '<dt>' . l($item['title'], $item['href']) . '</dt>';
      $output .= '<dd>' . filter_xss_admin($item['description']) . '</dd>';
    }
    $output .= '</dl>';
  }
  else {
    if (user_access('administer wsconfig types')) {
      $output = '<p>' . t('Web Service Configuration Entities cannot be added because you have not created any wsconfig types yet. Go to the <a href="@create-wsconfig-type">wsconfig type creation page</a> to add a new wsconfig type.', array('@create-wsconfig-type' => url('admin/structure/wsconfig_types/add'))) . '</p>';
    }
    else {
      $output = '<p>' . t('No web service configuration types have been created yet for you to use.') . '</p>';
    }
  }

  return $output;
}

/**
 * Sets the breadcrumb for administrative wsconfig pages.
 */
function wsconfig_set_breadcrumb() {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Administration'), 'admin'),
    l(t('Content'), 'admin/content'),
    l(t('Web Service Configurations'), 'admin/content/wsconfig'),
  );

  drupal_set_breadcrumb($breadcrumb);
}



