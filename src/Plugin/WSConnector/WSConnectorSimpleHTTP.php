<?php

namespace Drupal\wsdata\Plugin\WSConnector;

use \Drupal\wsdata\Plugin;

/**
 * HTTP Connector.
 *
 * @WSConnector(
 *   id = "WSConnectorSimpleHTTP",
 *   label = @Translation("Simple HTTP connector", context = "WSConnector"),
 * )
 */

class WSConnectorSimpleHTTP extends \Drupal\wsdata\Plugin\WSConnectorBase {
    public function getMethods() {
      return array(
        'multiple' => array(
          'get' => t('GET Request'),
          'post' => t('POST Request'),
        ),
      );
    }

  public function wscall($type, $method, $argument, $options) {
    return NULL;
  }

}
