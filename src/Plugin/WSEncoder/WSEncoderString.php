<?php

namespace Drupal\wsdata\Plugin\WSEncoder;

use Drupal\wsdata\Plugin;

/**
 *  String Encoder.
 *
 * @WSDecoder(
 *   id = "WSEncoderString",
 *   label = @Translation("String Encoder (Passes data as is)", context = "WSEncoder"),
 * )
 */

class WSEncoderString extends \Drupal\wsdata\Plugin\WSEncoderBase {

  // Decode the web service response string, and returns a structured data array
  public function encode(&$data, &$replacements, &$url) {}
}
