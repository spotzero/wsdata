<?php

namespace Drupal\wsdata\Plugin\WSConnector;

use Drupal\wsdata\Plugin;

/**
 * HTTP Connector.
 *
 * @WSConnector(
 *   id = "WSConnectorSimpleHTTP",
 *   label = @Translation("Simple HTTP connector", context = "WSConnector"),
 * )
 */

class WSConnectorSimpleHTTP extends WSConnectorBase {
    public function getMethods() {
      return array(
        'multiple' => array(
          'get' => t('GET Request'),
          'post' => t('POST Request'),
        ),
      );
    }
}
