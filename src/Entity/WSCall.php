<?php

namespace Drupal\wsdata\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines the Web Service Call entity.
 *
 * @ConfigEntityType(
 *   id = "wscall",
 *   label = @Translation("Web Service Call"),
 *   handlers = {
 *     "list_builder" = "Drupal\wsdata\WSCallListBuilder",
 *     "view_builder" = "Drupal\wsdata\WSCallViewBuilder",
 *     "form" = {
 *       "add" = "Drupal\wsdata\Form\WSCallForm",
 *       "edit" = "Drupal\wsdata\Form\WSCallForm",
 *       "delete" = "Drupal\wsdata\Form\WSCallDeleteForm",
 *       "test" = "Drupal\wsdata\Form\WSCallTestForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\wsdata\WSCallHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "wscall",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/wscall/{wscall}",
 *     "add-form" = "/admin/structure/wscall/add",
 *     "edit-form" = "/admin/structure/wscall/{wscall}/edit",
 *     "delete-form" = "/admin/structure/wscall/{wscall}/delete",
 *     "collection" = "/admin/structure/wscall"
 *   }
 * )
 */
class WSCall extends ConfigEntityBase implements WSCallInterface {
  use StringTranslationTrait;

  /**
   * The Web Service Call ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Web Service Call label.
   *
   * @var string
   */
  protected $label;

  public $options;

  public $wsserver;
  public $wsdecoder;
  public $wsencoder;

  protected $wsserverInst;
  protected $wsdecoderInst;
  protected $wsencoderInst;
  protected $status;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    $this->wsserverInst = entity_load('wsserver', $this->wsserver);
    // Set the decoder instance.
    $wsdecoderManager = \Drupal::service('plugin.manager.wsdecoder');
    $wspdefs = $wsdecoderManager->getDefinitions();
    $this->wsdecoderInst = $wsdecoderManager->createInstance($this->wsdecoder);

    // Set the enocder instance.
    $wsencoderManager = \Drupal::service('plugin.manager.wsencoder');
    $wspenfs = $wsencoderManager->getDefinitions();
    $this->wsencoderInst = $wsencoderManager->createInstance($this->wsencoder);

    $this->status = [];
  }

  /**
   * {@inheritdoc}
   */
  public function setEndpoint($endpoint) {
    if ($this->wsserverInst) {
      $this->wsserverInst->setEndpoint($endpoint);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getEndpoint() {
    return $this->wsserverInst ? $this->wsserverInst->getEndpoint() : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function call($method = NULL, $replacements = [], $data = NULL, $options = [], $key = NULL, $tokens = [], $cache_tag = [], &$context = [])  {
    $this->status = [
      'method' => $method,
      'status' => 'called',
    ];

    if (!$this->wsserverInst) {
      $this->status['status'] = 'error';
      $this->status['error_message'] = $this->t('No WSServer Instance to found.');
      $this->Status['error'] = TRUE;
      return FALSE;
    }
    // Build out the Cache ID based on the parameters passed.
    $conn = $this->getConnector();
    $cid_array = array_merge(
      $this->getOptions(),
      $options,
      $replacements,
      $tokens,
      [
        'data' => $data,
        'key' => $key,
        'conn' => $conn->getCache(),
      ]
    );
    $cid = md5(serialize($cid_array));
    $this->status['cache']['cid'] = $cid;
    $this->status['cache']['cached'] = FALSE;
    $this->status['called'] = FALSE;

    // Try to retrieve the data from cache.
    if ($cache = \Drupal::cache('wsdata')->get($cid)) {
      $this->status['status'] = 'success';
      $this->status['cache']['cached'] = FALSE;
      $cache_data = $cache->data;

      if ($this->wsdecoderInst->isCacheable()) {
        $this->status['cache']['debug'] = 'Returning parsed data from cache';
        return $cache_data;
      }

      $this->status['cache']['debug'] = 'Loaded WSCall result from cache and parsed the data';
      $this->addData($cache_data);
      return $this->getData($key);
    }

    // Try to make the call.
    $options = array_merge($this->getOptions(), $options);

    if ($method and !in_array($method, $conn->getMethods())) {
      throw new WSDataInvalidMethodException(sprintf('Invalid method %s on connector type %s', $method, $this->wsserverInst->wsconnector));
    }
    elseif (isset($options['method']) and in_array($options['method'], $conn->getMethods())) {
      $method = $options['method'];
    }
    else {
      $methods = $conn->getMethods();
      $method = reset($methods);
    }

    $context = [
      'replacements' => $replacements,
      'data' => $data,
      'options' => &$options,
      'key' => $key,
      'tokens' => $tokens,
    ];

    // Encode the payload data.
    $this->wsencoderInst->encode($data, $replacements, $options['path'], $context);

    // Call the connector.
    $result = $conn->call($options, $method, $replacements, $data, $tokens);
    $this->status['call-status'] = $conn->getStatus();
    // Handle error case.
    if(!empty($conn->getError())) {
      $this->status['error'] = TRUE;

      $message = $this->t(
        'wsdata %wsdata_name failed with error %code %message',
        [
          '%wsdata_name' => $this->id,
          '%code' => $conn->getError()['code'],
          '%message' => $conn->getError()['message']
        ]
      );
      $this->status['error_message'] = $message;
      \Drupal::logger('wsdata')->error($message);
      return FALSE;
    }

    $this->addData($result, $context);
    $data = $this->getData($key);

    $this->status['called'] = TRUE;
    $this->status['cache']['wsencoder'] = $this->wsencoderInst->isCacheable();
    $this->status['cache']['wsdecoder'] = $this->wsdecoderInst->isCacheable();
    $this->status['cache']['wsconnect'] = $conn->supportsCaching($method);
    $this->status['cache']['expires'] = $conn->expires();
    $expires = time() + $conn->expires();

    // Fetch the cache tags for this call and the server instance call.
    $cache_tags = array_merge($this->wsserverInst->getCacheTags(), $this->getCacheTags(), $cache_tag);
    $this->status['cache']['tags'] = $cache_tags;

    if ($conn->supportsCaching($method) && $this->wsencoderInst->isCachable()) {
      if ($this->wsdecoderInst->isCacheable()) {
        $this->status['cache']['debug'] = 'Caching the parsed results of the WSCall.';
        \Drupal::cache('wsdata')->set($cid, $data, $expires, $cache_tags);
      } else {
        $this->status['cache']['debug'] = 'Caching the verbatim result of the WSCall';
        \Drupal::cache('wsdata')->set($cid, $result, $expires, $cache_tags);
      }
    }
    else {
      $this->status['cache']['debug'] = 'Result is not cachable';
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   *
   * Doesn't save the WSCall though.
   */
  public function setOptions($values = []) {
    if (!isset($this->wsserverInst)) {
      $this->wsserverInst = entity_load('wsserver', $values['wsserver']);
    }
    $this->options[$this->wsserver] = $this->wsserverInst->wsconnectorInst->saveOptions($values);


    $this->needSave = TRUE;
  }

  public function lastCallStatus() {
    return $this->status;
  }

  /**
   * {@inheritdoc}
   */
  public function getReplacements() {
    return $this->wsserverInst ? $this->wsserverInst->wsconnectorInst->getReplacements($this->getOptions()) : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionsForm($wsserver = NULL, $options = []) {
    if (isset($wsserver)) {
      $wsserverInst = entity_load('wsserver', $wsserver);
      return $wsserverInst->wsconnectorInst->getOptionsForm($options);
    }
    if (isset($this->wsserverInst->wsconnectorInst)) {
      return $this->wsserverInst->wsconnectorInst->getOptionsForm($options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return isset($this->options[$this->wsserver]) ? $this->options[$this->wsserver] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getMethods() {
    return $this->wsserverInst->getMethods();
  }

  /**
   * {@inheritdoc}
   */
  public function addData($data, $context = []) {
    if (!isset($this->wsdecoderInst)) {
      $wsdecoderManager = \Drupal::service('plugin.manager.wsdecoder');
      $this->wsdecoderInst = $wsdecoderManager->createInstance($this->wsdecoder);
    }
    return $this->wsdecoderInst->addData($data, NULL, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function getData($key = NULL) {
    if (isset($this->wsdecoderInst)) {
      return $this->wsdecoderInst->getData($key);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getConnector() {
    return $this->wsserverInst ? $this->wsserverInst->getConnector() : FALSE;
  }
}
