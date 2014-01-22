<?php

/**
 * @file
 * Aggregates operations that can be done on a wsconfig under a single field
 *
 * @author Mathew Winstone <mwinstone@coldfrontlabs.ca>
 * @copyright 2011 Coldfront Labs Inc.
 * @license Copyright (c) 2011 All rights reserved
 */

class wsconfig_handler_wsconfig_operations_field extends views_handler_field {
  function construct() {
    parent::construct();

    $this->additional_fields['wsconfig_id'] = 'wsconfig_id';
  }

  function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  function render($values) {

    $links = menu_contextual_links('wsconfig', 'admin/structure/wsconfig/', array($this->get_value($values, 'wsconfig_id')));
    
    if (!empty($links)) {
      return theme('links', array('links' => $links, 'attributes' => array('class' => array('links', 'inline', 'operations'))));
    }
  }
}
