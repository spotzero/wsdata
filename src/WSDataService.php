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
  public function call($wscall, $method = NULL, $replacements = [], $data = NULL, $options = [], $key = NULL, $tokens = []) {
    if (!is_object($wscall)) {
      $wscall = $this->entity_type_manager->getStorage('wscall')->load($wscall);
    }

    $data = $wscall->call($method, $replacements, $data, $options, $key, $tokens);
    return $data;
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
