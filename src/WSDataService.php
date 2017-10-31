<?php

namespace Drupal\wsdata;

use Drupal\Core\Entity\EntityTypeManager;

/**
 * Service for processing WSData requests.
 */
class WSDataService {
  protected $error;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->entity_type_manager = $entity_type_manager;
  }

  /**
   * Call method to make the WSCall.
   */
  public function call($wscall, $method = NULL, $replacements = [], $data = NULL, $options = [], $key = NULL) {
    if (!is_object($wscall)) {
      $wscall = $this->entity_type_manager->getStorage('wscall')->load($wscall);
    }

    $options = array_merge($options,  $wscall->getOptions());
    $conn = $wscall->getConnector();

    if ($method and !in_array($method, $conn->getMethods())) {
      throw new WSDataInvalidMethodException(sprintf('Invalid method %s on connector type %s', $method, $wscall->wsserverInst->wsconnector));
    }
    elseif (isset($options['method']) and in_array($options['method'], $conn->getMethods())) {
      $method = $options['method'];
    }
    else {
      $methods = $conn->getMethods();
      $method = reset($methods);
    }

    $data = $conn->call($options, $method, $replacements, $data);
    if ($data) {
      $wscall->addData($data);
      return $wscall->getData($key);
    } else {
      $this->error = $conn->getError();
      return FALSE;
    }
  }

  public function getError() {
    if ($this->error) {
      $error = $this->error;
      $this->error = NULL;
      return $error;
    }
    return NULL;
  }

}
