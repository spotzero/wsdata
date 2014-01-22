<?php

/**
 * @file
 * Delete link field handler for wsconfig
 *
 * @author Mathew Winstone <mwinstone@coldfrontlabs.ca>
 * @copyright 2011 Coldfront Labs Inc.
 * @license Copyright (c) 2011 All rights reserved
 */

class wsconfig_handler_delete_link_field extends wsconfig_handler_link_field {
  function construct() {
    parent::construct();
    $this->additional_fields['type'] = 'type';
  }


  function render($values) {
    $type = $values->{$this->aliases['type']};
    
    //Creating a dummy model to check access against
    $dummy_wsconfig = (object) array('type' => $type);
    if (!wsconfig_access('edit', $dummy_wsconfig)) {
      return;
    }
    
    $text = !empty($this->options['text']) ? $this->options['text'] : t('delete');
    $model_id = $values->{$this->aliases['wsconfig_id']};
    
    return l($text, 'admin/structure/wsconfig/manage/' . $model_id . '/delete');
  }
}
