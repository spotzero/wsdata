<?php

namespace Drupal\wsdata\Plugin\WSConnector;

use Drupal\Core\Utility\Token;
use Drupal\wsdata\WSDataInvalidMethodException;
use Drupal\wsdata\Plugin\WSConnectorBase;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * HTTP Connector.
 *
 * @WSConnector(
 *   id = "WSConnectorGraphQL",
 *   label = @Translation("GraphQL Connector", context = "WSConnector"),
 * )
 */
class WSConnectorGraphQL extends WSConnectorSimpleHTTP {

  /**
   * {@inheritdoc}
   */
  public function getMethods() {
    return ['post'];
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return [
      'path' => NULL,
      'method' => [],
      'headers' => [],
      'query' => '',
      'operationName' => '',
      'variables' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getReplacements(array $options) {
    return array_unique($this->findTokens($this->endpoint . '/' . $options['path'])
      + $this->findTokens($options['query'])
      + $this->findTokens(json_encode($options['variables'])));
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionsForm($options = []) {
    $form = parent::getOptionsForm($options);
    $form['query'] = [
      '#type' => 'textarea',
      '#title' => $this->t('GraphQL Query'),
      '#required' => TRUE,
    ];

    $form['operationName'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Operation Name (optional)'),
    ];

    $form['variables'] = [
      '#type' => 'textarea',
      '#title' => $this->t('GraphQL Variables (optional)'),
    ];
    return $form;
  }

    /**
   * {@inheritdoc}
   */
  public function call($options, $method, $replacements = [], $data = NULL, array $tokens = []) {
    $contenttype = FALSE;
    if (!isset($options['headers'])) {
      $options['headers'] = [];
    }
    foreach ($options['headers'] as $key => $header) {
      if (strtolower($header['key_' . $key]) == strtolower('Content-Type')) {
        $contenttype = TRUE;
      }
    }
    if (!$contenttype) {
      $i = sizeof($options['headers']);
      $options['headers'][$i] = [
        'key_' . $i => 'Content-Type',
        'value_' . $i => 'application/json',
      ];
    }

    $graphql = [
      'query' => $options['query'],
      'operationName' => !empty($options['operationName']) ? $options['operationName'] : NULL,
      'variables' => json_decode($options['variables']),
    ];

    unset($options['query']);
    unset($options['operationName']);
    unset($options['variables']);

    $data = json_encode($graphql);

    return parent::call($options, $method, $replacements, $data, $tokens);
  }
}
