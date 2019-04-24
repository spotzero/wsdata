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
        '#title' => $this->t('Path'),
        '#description' => $this->t('The final endpoint will be <em>Server Endpoint/Path</em>'),
        '#type' => 'textfield',
      ],
      'methods' => [
        '#title' => $this->t('Supported Operations'),
        '#type' => 'checkboxes',
        '#options' => [
          'create' => $this->t('RESTful create method (POST to <em>Endpoint/Path</em>)'),
          'read' => $this->t('RESTful read method (GET to <em>Endpoint/Path/ID</em>)'),
          'update' => $this->t('RESTful update method (PUT to <em>Endpoint/Path/ID</em>)'),
          'delete' => $this->t('RESTful delete method (DELETE to <em>Endpoint/Path/ID</em>)'),
          'index' => $this->t('RESTful index method (GET to <em>Endpoint/Path</em>)'),
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
