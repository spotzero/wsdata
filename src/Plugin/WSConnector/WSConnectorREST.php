<?php

namespace Drupal\wsdata\Plugin\WSConnector;

use Drupal\wsdata\Plugin;

/**
 * REST Connector.
 *
 * @WSConnector(
 *   id = "WSConnectorREST",
 *   label = @Translation("RESTful Connector", context = "WSConnector"),
 * )
 */

class WSConnectorREST extends \Drupal\wsdata\Plugin\WSConnectorBase {
    public function getMethods() {
      return array(
        'single' => array(
          'create' => t('RESTful create method (POST)'),
          'read' => t('RESTful read method (GET)'),
          'update' => t('RESTful update method (PUT)'),
          'delete' => t('RESTful delete method (DELETE)'),
          'index' => t('RESTful index method (GET)'),
        ),
        'multiple' => array(
          'action' => t('RESTful action (POST)'),
        ),
      );
    }

  public function wscall($type, $method, $argument, $options) {
    return NULL;
  }
}
