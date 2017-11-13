<?php

namespace Drupal\wsdata\Plugin\WSConnector;

use Drupal\wsdata\Plugin\WSConnectorBase;

/**
 * REST Connector.
 *
 * @WSConnector(
 *   id = "WSConnectorREST",
 *   label = @Translation("RESTful Connector", context = "WSConnector"),
 * )
 */
class WSConnectorREST extends WSConnectorSimpleHTTP {

  /**
   * {@inheritdoc}
   */
  public function getMethods() {
    return ['create', 'read', 'update', 'delete', 'index'];
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return [
      'path' => NULL,
      'methods' => [],
    ];
  }


  /**
   * {@inheritdoc}
   */
  public function getReplacements(array $options) {
    return $this->findTokens($options['path']);
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionsForm($options = []) {
    return [
      'path' => [
        '#title' => t('Path'),
        '#description' => t('The final endpoint will be <em>Server Endpoint/Path</em>'),
        '#type' => 'textfield',
      ],
      'methods' => [
        '#title' => t('Supported Operations'),
        '#type' => 'checkboxes',
        '#options' => [
          'create' => t('RESTful create method (POST to <em>Endpoint/Path</em>)'),
          'read' => t('RESTful read method (GET to <em>Endpoint/Path/ID</em>)'),
          'update' => t('RESTful update method (PUT to <em>Endpoint/Path/ID</em>)'),
          'delete' => t('RESTful delete method (DELETE to <em>Endpoint/Path/ID</em>)'),
          'index' => t('RESTful index method (GET to <em>Endpoint/Path</em>)'),
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function call($options, $method, $replacements = [], $data = NULL, array $tokens = []) {
    return NULL;
  }

}
