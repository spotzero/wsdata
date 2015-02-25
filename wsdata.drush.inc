<?php

/**
 * @file
 * Drush implementation for the wsconfig module.
 */

/**
 * Implementats hook_drush_command().
 */
function wsdata_drush_command() {
  $items = array();
  $items['wsdata-get-endpoint'] = array(
    'description' => 'Get wsdata endpoint by wsdata config type. This will display the endpoint for the specified type',
    'aliases' => array('wsdata-get-ep','wctype-get-endpoint'),
    'arguments' => array(
      'type' => 'The type you wish to show the endpoint for, see drush wsdata-list-types .',
    ),
    'examples' => array(
      'drush wsdata-get-ep "type"' => 'gt the endpoint for this wsdata config type.',
    ),
    'drupal dependencies' => array('wsconfig','wsdata'),
  );
  $items['wsdata-set-endpoint'] = array(
    'description' => 'Set wsdata endpoint by wsdata config type. This will set the endpoints for all wsconfig of the specified type',
    'aliases' => array('wsdata-set-ep','wctype-set-endpoint'),
    'arguments' => array(
      'uri' => 'A uri for the endpoint.',
      'type' => 'The type you wish to update, for list, drush wsdata-list-types .',
    ),
    'examples' => array(
      'drush wsdata-set-ep http://endpoint/services/rest "type"' => 'set the endpoint to http://endpoint/services/rest for this wsdata config type.',
    ),
    'drupal dependencies' => array('wsconfig','wsdata'),
  );
  $items['wsdata-list-types'] = array(
    'arguments' => array(),
    'description' => 'Show all wsdata endpoints by uri and endpoint type.',
    'examples' => array(
      'drush wsdata-list-types' => 'Show / list summary of all wsdata endpoints.',
    ),
    'drupal dependencies' => array('wsconfig','wsdata'),
    'aliases' => array('wsdata-list','wsd-list-types'),
  );
  return $items;
}

/**
 * Implementats of hook_drush_help().
 */
function wsdata_drush_help($section) {
  switch ($section) {
    case 'drush:wsdata-get-endpoint':
      return dt("This command will get the endpoint of your wsconfig entry by the type of the entry.");
    case 'drush:wsdata-set-endpoint':
      return dt("This command will set the endpoint of your wsconfig entry by the type of the entry.  This is useful when changing your server environments.");
    case 'drush:wsdata-list-types':
      return dt("This command will show pertinent details about wsconfig types including endpoint configured for wsdata.");
  }
}

/**
 * Drush command callback.
 */
function drush_wsdata_set_endpoint() {
  if ($args = func_get_args()) {
    $update_option = 'simple';
    // list the arguments.
    drush_print();
    drush_print('new endpoint uri  = ' . $args[0]);
    drush_print('new endpoint type = ' . $args[1]);
    drush_print();
  } elseif ( empty($args[0]) || empty($args[1]) ) {
    drush_print(dt("Please specify an uri and type of endpoint.  Please see: drush help wsdata-set-endpoint"));
    return drush_set_error('arguments_invalid', dt('Invalid arguments or arguments not provided, please see drush help wsdata-set-endpoint'));
  }
  drush_log(dt('Beginning wsdata set endpoint operations by config type.'), 'ok');
  module_load_include('module', 'wsconfig', 'wsconfig.module');
  $wsconfigtype = wsconfig_type_load($args[1]);
  if (strripos($args[0], "http") === FALSE) {
    return drush_set_error('arguments_invalid', dt('Endpoint url did not pass validation, please see drush help wsdata-set-endpoint'));
  }
  if (isset($wsconfigtype) && ($wsconfigtype != NULL)) {
    $wsconfigtype->setEndpoint($args[0]);
    if ($wsconfigtype->setEndpoint($args[0])) {
      drush_print(dt("endpoint is set but not yet saved, next prompt is for save"));
    } else {
      drush_print(dt("échoué"));
    }
  } else {
    drush_wsdata_list_types();
    return drush_set_error('arguments_invalid', dt('WSConfig type !type was not found, please select from one of the above or run drush wsdata-list-types', array('!type' => $args[1])));
  }
  // Determine if -y was supplied. If so, we can filter out needless output
  // from this command.
  $skip_confirmation = drush_get_context('DRUSH_AFFIRMATIVE');
  if (!drush_confirm(dt('Are you sure?'))) {
    return drush_user_abort();
  }

  drush_wsdata_list_types();
  drush_print('');
  if ($wsconfigtype->save()) {
    drush_print(dt("save success"));
  } else {
    drush_print(dt("save fail"));
  }
  drush_print('');
  drush_print('Endpoint for ' . $args[1] . ' is now: ' . $args[1]);
  //drush_print(print_r(get_class_methods($wsconfigtype)));
  //drush_print(print_r(get_class_methods($wsconfig)));
}

/**
 * Get a list of all wsdata config types.
 */
function drush_wsdata_list_types() {
  $query = db_select('wsconfig_type', 'wt');
  $query->fields('wt', array('type', 'label' ,'data'))
        ->orderBy('type', 'DESC');

  $result = $query->execute();

  drush_print('');
  while($record = $result->fetchObject()) {
    $data = unserialize($record->data);
    drush_print('wsdata config type           = ' . $record->type);
    drush_print('wsdata config type label     = ' . $record->label);
    drush_print('wsdata config type endpoint  = ' . $data['endpoint']);
    drush_print('wsdata config type connector = ' . $data['connector']);
    drush_print('');
  }
}

/**
 * Get an endpoint by type.
 */
function drush_wsdata_get_endpoint() {
  if ($args = func_get_args()) {
  } elseif ( empty($args[0]) ) {
    drush_print(dt("Please specify a type.  Please see: drush help wsdata-get-endpoint"));
    return drush_set_error('arguments_invalid', dt('Invalid arguments or arguments not provided, please see drush help wsdata-get-endpoint'));
  }
  $query = db_select('wsconfig_type', 'wt');
  $query->fields('wt', array('type', 'label' ,'data'))
        ->condition('type', $args[0])
        ->orderBy('type', 'DESC');

  $result = $query->execute();

  while($record = $result->fetchObject()) {
    $data = unserialize($record->data);
    drush_print($data['endpoint']);
  }
}
