<?php

/**
 * @file
 * Adds Views integration to wsconfig
 *
 * @author Mathew Winstone <mwinstone@coldfrontlabs.ca>
 * @copyright 2011 Coldfront Labs Inc.
 * @license Copyright (c) 2011 All rights reserved
 */

/**
 * Implements hook_views_data_alter().
 */
function wsconfig_views_data_alter(&$data) {
  $data['wsconfig']['link_wsconfig'] = array(
    'field' => array(
      'title' => t('Link'),
      'help' => t('Provide a link to the web service configuration.'),
      'handler' => 'wsconfig_handler_link_field',
    ),
  );
  $data['wsconfig']['edit_wsconfig'] = array(
    'field' => array(
      'title' => t('Edit Link'),
      'help' => t('Provide an edit link to the web service configuration.'),
      'handler' => 'wsconfig_handler_edit_link_field',
    ),
  );
  $data['wsconfig']['delete_wsconfig'] = array(
    'field' => array(
      'title' => t('Delete Link'),
      'help' => t('Provide a delete link to the web service configuration.'),
      'handler' => 'wsconfig_handler_delete_link_field',
    ),
  );
  $data['wsconfig']['operations'] = array(
    'field' => array(
      'title' => t('Operations links'),
      'help' => t('Display all operations available for this web service configuration.'),
      'handler' => 'wsconfig_handler_wsconfig_operations_field',
    ),
  );
}

/**
 * Implements hook_views_default_views();
 */
function wsconfig_views_default_views() {
  $views = array();
  
  $view = new view;
  $view->name = 'web_service_configurations';
  $view->description = 'List of web service configurations';
  $view->tag = 'default';
  $view->base_table = 'wsconfig';
  $view->human_name = 'Web Service Configurations';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Web Service Configurations';
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['query']['options']['query_comment'] = FALSE;
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '50';
  $handler->display->display_options['style_plugin'] = 'table';
  /* Field: Web Service Configuration: Web service configuration ID */
  $handler->display->display_options['fields']['wsconfig_id']['id'] = 'wsconfig_id';
  $handler->display->display_options['fields']['wsconfig_id']['table'] = 'wsconfig';
  $handler->display->display_options['fields']['wsconfig_id']['field'] = 'wsconfig_id';
  $handler->display->display_options['fields']['wsconfig_id']['label'] = 'ID';
  $handler->display->display_options['fields']['wsconfig_id']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['wsconfig_id']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['wsconfig_id']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['wsconfig_id']['alter']['external'] = 0;
  $handler->display->display_options['fields']['wsconfig_id']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['wsconfig_id']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['wsconfig_id']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['wsconfig_id']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['wsconfig_id']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['wsconfig_id']['alter']['more_link'] = 0;
  $handler->display->display_options['fields']['wsconfig_id']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['wsconfig_id']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['wsconfig_id']['alter']['html'] = 0;
  $handler->display->display_options['fields']['wsconfig_id']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['wsconfig_id']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['wsconfig_id']['hide_empty'] = 0;
  $handler->display->display_options['fields']['wsconfig_id']['empty_zero'] = 0;
  $handler->display->display_options['fields']['wsconfig_id']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['wsconfig_id']['format_plural'] = 0;
  /* Field: Web Service Configuration: Name */
  $handler->display->display_options['fields']['name']['id'] = 'name';
  $handler->display->display_options['fields']['name']['table'] = 'wsconfig';
  $handler->display->display_options['fields']['name']['field'] = 'name';
  $handler->display->display_options['fields']['name']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['name']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['name']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['name']['alter']['external'] = 0;
  $handler->display->display_options['fields']['name']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['name']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['name']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['name']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['name']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['name']['alter']['more_link'] = 0;
  $handler->display->display_options['fields']['name']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['name']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['name']['alter']['html'] = 0;
  $handler->display->display_options['fields']['name']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['name']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['name']['hide_empty'] = 0;
  $handler->display->display_options['fields']['name']['empty_zero'] = 0;
  $handler->display->display_options['fields']['name']['hide_alter_empty'] = 1;
  /* Field: Web Service Configuration: Link */
  $handler->display->display_options['fields']['link_wsconfig']['id'] = 'link_wsconfig';
  $handler->display->display_options['fields']['link_wsconfig']['table'] = 'wsconfig';
  $handler->display->display_options['fields']['link_wsconfig']['field'] = 'link_wsconfig';
  $handler->display->display_options['fields']['link_wsconfig']['label'] = '';
  $handler->display->display_options['fields']['link_wsconfig']['element_label_colon'] = FALSE;
  /* Field: Web Service Configuration: Edit Link */
  $handler->display->display_options['fields']['edit_wsconfig']['id'] = 'edit_wsconfig';
  $handler->display->display_options['fields']['edit_wsconfig']['table'] = 'wsconfig';
  $handler->display->display_options['fields']['edit_wsconfig']['field'] = 'edit_wsconfig';
  $handler->display->display_options['fields']['edit_wsconfig']['label'] = '';
  $handler->display->display_options['fields']['edit_wsconfig']['element_label_colon'] = FALSE;
  /* Field: Web Service Configuration: Delete Link */
  $handler->display->display_options['fields']['delete_wsconfig']['id'] = 'delete_wsconfig';
  $handler->display->display_options['fields']['delete_wsconfig']['table'] = 'wsconfig';
  $handler->display->display_options['fields']['delete_wsconfig']['field'] = 'delete_wsconfig';
  $handler->display->display_options['fields']['delete_wsconfig']['label'] = '';
  $handler->display->display_options['fields']['delete_wsconfig']['element_label_colon'] = FALSE;

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['path'] = 'admin/content/wsconfig/list';
  $handler->display->display_options['menu']['type'] = 'default tab';
  $handler->display->display_options['menu']['title'] = 'List';
  $handler->display->display_options['menu']['weight'] = '0';
  $handler->display->display_options['menu']['context'] = 0;
  $handler->display->display_options['tab_options']['type'] = 'tab';
  $handler->display->display_options['tab_options']['title'] = 'Web Service Configurations';
  $handler->display->display_options['tab_options']['weight'] = '0';
  
  $views[] = $view;
  
  return $views;
}