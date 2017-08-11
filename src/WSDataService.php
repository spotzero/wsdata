<?php

namespace Drupal\wsdata;

use Drupal\Core\Entity\EntityTypeManager;

/**
 * Service for processing WSData requests.
 */
class WSDataService {

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
    $wsc = $this->entity_type_manager->getStorage('wscall')->load($wscall);
    $opt = $wsc->getOptions();
    $opt['options'] = $options;
    $conn = $wsc->getConnector();

    if ($method and !in_array($method, $conn->getMethods())) {
      throw new WSDataInvalidMethodException(sprintf('Invalid method %s on connector type %s', $method, $wsc->wsserverInst->wsconnector));
    }
    else {
      $methods = $conn->getMethods();
      $method = reset($methods);
    }

    $data = $conn->call($opt, $method, $replacements, $data);
    $wsc->addData($data);
    return $wsc->getData($key);
  }

}
