<?php

namespace Drupal\wsdata\Plugin\WSEncoder;

use Drupal\wsdata\Plugin;

/**
 *  String Encoder.
 *
 * @WSEncoder(
 *   id = "WSEncoderJSON",
 *   label = @Translation("JSON Encoder", context = "WSEncoder"),
 * )
 */

class WSEncoderJSON extends \Drupal\wsdata\Plugin\WSEncoderBase {

  // Decode the web service response string, and returns a structured data array
  public function encode(&$data, &$replacements, &$url) {
    $data = Json::encode($data);
    return;
  }
}
