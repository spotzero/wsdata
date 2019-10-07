<?php

namespace Drupal\wsdata\Plugin\WSConnector;

use Drupal\Core\Utility\Token;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;

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
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Client $http_client,
    Token $token,
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $http_client, $token, $language_manager);
    $this->http_client = $http_client;
    $this->token = $token;
    $this->language_manager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client'),
      $container->get('token'),
      $container->get('language_manager')
    );
  }

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
      'path' => '',
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
    $replacements = array_unique($this->findTokens($this->endpoint . '/' . $options['path'])
      + $this->findTokens($options['query'])
      + $this->findTokens(json_encode($options['variables'])));
    unset($replacements[array_search('LANGUAGE', $replacements)]);
    return $replacements;
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
    $langcode = $options['langcode'] ?? $this->language_manager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    $replacements['LANGUAGE'] = strtoupper($langcode);

    $contenttype = FALSE;
    if (!isset($options['headers'])) {
      $options['headers'] = [];
    }
    foreach ($options['headers'] as $key => $header) {
      if (isset($header['key_' . $key]) and strtolower($header['key_' . $key]) == strtolower('Content-Type')) {
        $contenttype = TRUE;
      }
    }
    if (!$contenttype) {
      $i = count($options['headers']);
      $options['headers'][$i] = [
        'key_' . $i => 'Content-Type',
        'value_' . $i => 'application/json',
      ];
    }

    $graphql = [
      'query' => $this->applyReplacements($options['query'], $replacements, $tokens),
      'operationName' => !empty($options['operationName']) ? $options['operationName'] : NULL,
      'variables' => json_decode($options['variables']),
    ];

    unset($options['query']);
    unset($options['operationName']);
    unset($options['variables']);

    $data = json_encode($graphql);

    return parent::call($options, $method, $replacements, $data, $tokens);
  }

  /**
   * {@inheritdoc}
   */
  public function getCache() {
    return $this->language_manager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
  }

}
