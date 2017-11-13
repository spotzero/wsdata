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
 *   id = "WSConnectorSimpleHTTP",
 *   label = @Translation("Simple HTTP connector", context = "WSConnector"),
 * )
 */
class WSConnectorSimpleHTTP extends WSConnectorBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(
         array $configuration,
         $plugin_id,
         $plugin_definition,
         Client $http_client,
         Token $token
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $token);
    $this->http_client = $http_client;
    $this->token = $token;
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
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getMethods() {
    return ['get', 'post', 'put', 'delete', 'head', 'options'];
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return [
      'path' => NULL,
      'method' => [],
      'headers' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function saveOptions($values) {
    // Check how many key values and create the array.
    foreach ($values as $key => $value) {
      if (preg_match("/^key_([0-9]+)/", $key, $matches)) {
        if (isset($matches[1]) && !empty($values['key_' . $matches[1]])) {
          $values['headers'][$matches[1]] = array('key_' . $matches[1] => $values['key_' . $matches[1]],
                                                  'value_' . $matches[1] => $values['value_' . $matches[1]]);
          unset($values['key_' . $matches[1]]);
          unset($values['value_' . $matches[1]]);
        }
      }
    }
    return parent::saveOptions($values);
  }

  /**
   * {@inheritdoc}
   */
  public function getReplacements(array $options) {
    return $this->findTokens($this->endpoint . '/' . $options['path']);
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionsForm($options = []) {

    $methods = $this->getMethods();
    $form['path'] = [
      '#title' => t('Path'),
      '#description' => t('The final endpoint will be <em>Server Endpoint/Path</em>'),
      '#type' => 'textfield',
      '#maxlength' => 512,
    ];

    $form['method'] = [
      '#title' => t('HTTP Method'),
      '#type' => 'select',
      '#options' => array_combine($methods, $methods),
    ];

    $header_count = 1;

    if (isset($options['form_state'])) {
      if ($options['form_state']->get('header_count')) {
        $header_count = $options['form_state']->get('header_count');
      }
      else {
        $options['form_state']->set('header_count', 1);
      }
    }

    $form['headers'] = [
      '#title' => t('Headers'),
      '#type' => 'fieldset',
      '#attributes' => ['id' => 'wsconnector-headers'],
    ];

    for($i = 0; $i < $header_count; $i++) {
      $form['headers'][$i]['key_' . $i] = [
        '#type' => 'textfield',
        '#title' => t('Key'),
      ];

      $form['headers'][$i]['value_' . $i] = [
        '#type' => 'textfield',
        '#title' => t('Value'),
      ];
    }

    if (isset($options['form_state'])) {
      $form['headers']['add_another'] = [
        '#type'   => 'submit',
        '#value'  => t('Add another'),
        '#submit' => ['Drupal\wsdata\Plugin\WSConnector\WSConnectorSimpleHTTP::wsconnector_http_header_add_item'],
        '#ajax'   => [
          'callback' => 'Drupal\wsdata\Plugin\WSConnector\WSConnectorSimpleHTTP::wsconnector_http_header_ajax_callback',
          'wrapper'  => 'wsconnector-headers',
        ],
      ];
    }

    return $form;
  }

  public static function wsconnector_http_header_add_item(array &$form, FormStateInterface $form_state) {
    $count = $form_state->get('header_count');
    $form_state->set('header_count', ($count + 1));
    $form_state->setRebuild();
  }
  /**
   * Ajax callback function.
   */
  public static function wsconnector_http_header_ajax_callback(array &$form, FormStateInterface $form_state) {
    return $form['headers'];
  }

  /**
   * {@inheritdoc}
   */
  public function call($options, $method, $replacements = [], $data = NULL, array $tokens = []) {
    if (!in_array($method, $this->getMethods())) {
      throw new WSDataInvalidMethodException(sprintf('Invalid method %s on connector type %s', $method, __CLASS__));
    }

    $uri = $this->endpoint . '/' . $options['path'];
    $uri = $this->applyReplacements($uri, $replacements, $tokens);
    $options['http_errors'] = FALSE;

    // Perform the token replace on the headers.
    if (!empty($options['headers'])) {
      $token_service = \Drupal::token();
      for ($i = 0; $i < count($options['headers']); $i++) {
        if (!empty($options['headers'][$i]['key_' . $i])) {
          $options['headers'][$options['headers'][$i]['key_' . $i]] = $token_service->replace($options['headers'][$i]['value_' . $i], $tokens);
        }
        unset($options['headers'][$i]['key_' . $i]);
        unset($options['headers'][$i]['value_' . $i]);
        unset($options['headers'][$i]);
      }
    }
    if (!empty($data)) {
      $options['body'] = $data;
    }

    $response = $this->http_client->request($method, $uri, $options);
    $status = $response->getStatusCode();

    if ($status >= 199 and $status <= 300) {
      return (string)$response->getBody();
    }

    $this->setError($status, $response->getReasonPhrase());
    return FALSE;
  }
}


