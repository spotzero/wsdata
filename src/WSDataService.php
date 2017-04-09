<?php

namespace Drupal\wsdata;

class WSDataService {
  /**
   * Constructor.
   */
  public function __construct() {

  }

  public function call($wscall, $method = NULL, $replacements = array(), $data = NULL, $options = array(), $key = NULL) {
    $wsc = entity_load('wscall', $wscall);
    $opt = $wsc->getOptions();
    $opt['options'] = $options;
    $conn = $wsc->getConnector();

    if ($method and !in_array($method, $conn->getMethods())) {
      throw new WSDataInvalidMethodException(t('Invalid method @method on connector type @type', array('@method' => $method, '@type' => $wsc->wsserverInst->wsconnector)));
    }
    else {
      $methods = $conn->getMethods();
      $method =  reset($methods);
    }

    $data = $conn->call($opt, $method, $replacements, $data);
    $wsc->addData($data);
    return $wsc->getData($key);
  }

}
