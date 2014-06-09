<?php

function ws_performance_config_form($form, &$form_state) {
  $form['ws_performance_store_method'] = array(
    '#type' => 'checkbox',
    '#title' => t('Log the full method called by the WSConfig'),
    '#default_value' => variable_get('ws_performance_store_method', FALSE),
  );
  $form['ws_performance_store_data'] = array(
    '#type' => 'checkbox',
    '#title' => t('Log all data included include with the WSConfig call'),
    '#default_value' => variable_get('ws_performance_store_data', FALSE),
  );

  $form['ws_performance_store_result'] = array(
    '#type' => 'checkbox',
    '#title' => t('Log all results data retrived by the WSConfig call'),
    '#default_value' => variable_get('ws_performance_store_result', FALSE),
  );
  return $form;
}

function ws_performance_config_form_submit($form, &$form_state) {
  if (isset($form_state['values']['ws_performance_store_method'])) {
    variable_set('ws_performance_store_method', $form_state['values']['ws_performance_store_method']);
  }

  if (isset($form_state['values']['ws_performance_store_data'])) {
    variable_set('ws_performance_store_data', $form_state['values']['ws_performance_store_data']);
  }

  if (isset($form_state['values']['ws_performance_store_result'])) {
    variable_set('ws_performance_store_result', $form_state['values']['ws_performance_store_result']);
  }
}

function ws_performance_overview() {
  $form = array();

  return $form;
}

function ws_performance_report($wsconfig) {
  $form = array();

  return $form;
}

function ws_performance_specific_report($wsconfig, $event) {
  $form = array();

  return $form;
}
