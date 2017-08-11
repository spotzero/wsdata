<?php

namespace Drupal\wsdata\Plugin\WSEncoder;

use Drupal\wsdata\Plugin\WSEncoderBase;

/**
 * String Encoder.
 *
 * @WSEncoder(
 *   id = "WSEncoderJSON",
 *   label = @Translation("JSON Encoder", context = "WSEncoder"),
 * )
 */
class WSEncoderJSON extends WSEncoderBase {

  /**
   * Encode JSON.
   */
  public function encode(&$data, &$replacements, &$url) {
    $data = Json::encode($data);
  }

}
