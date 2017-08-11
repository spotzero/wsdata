<?php

namespace Drupal\wsdata\Plugin\WSConnector;

use Drupal\wsdata\Plugin\WSConnectorBase;

/**
 * HTTP Connector.
 *
 * @WSConnector(
 *   id = "WSConnectorSimpleHTTP",
 *   label = @Translation("Simple HTTP connector", context = "WSConnector"),
 * )
 */
class WSConnectorSimpleHTTP extends WSConnectorBase {

  /**
   * {@inheritdoc}
   */
  public function getMethods() {
    return ['call'];
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return [
      'path' => NULL,
      'method' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionsForm() {
    return [
      'path' => [
        '#title' => t('Path'),
        '#description' => t('The final endpoint will be <em>Server Endpoint/Path</em>'),
        '#type' => 'textfield',
      ],
      'method' => [
        '#title' => t('HTTP Method'),
        '#type' => 'select',
        '#options' => [
          'get' => 'GET',
          'post' => 'POST',
          'put' => 'PUT',
          'delete' => 'DELETE',
          'head' => 'HEAD',
          'options' => 'OPTIONS',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function call($options, $method, $replacements = [], $data = NULL) {
    // TODO: Implement.
    return NULL;
  }

}
