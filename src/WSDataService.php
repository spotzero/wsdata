<?php

namespace Drupal\wsdata;

class WSDataService {

	  /**
	   * Constructor.
	   */
	  public function __construct() {

	  }

	  public function call($wscall, $method = NULL, $replacements = array(), $data = NULL, $options = array()) {
	    $wsc = entity_load('wscall', $wscall);
			$opt = $wsc->getOptions();
			$opt['options'] = $options;
      $conn = &$wsc->wsserverInst->wsconnectorInst;

			if ($method and !in_array($method, $conn->getMethods())) {
				return FALSE;
			}
			else {
				$methods = $conn->getMethods();
				$method =  reset($methods);
			}

			$data = $conn->call($opt, $method, $replacements, $data);
	    return $data;
	  }

}
