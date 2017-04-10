<?php

namespace Drupal\wsdata\Plugin\WSDecoder;

use Drupal\wsdata\Plugin;

/**
 *  String Decoder.
 *
 * @WSDecoder(
 *   id = "WSDecoderString",
 *   label = @Translation("String Decoder (Passes data as is)", context = "WSDecoder"),
 * )
 */

class WSDecoderString extends \Drupal\wsdata\Plugin\WSDecoderBase {

  // Decode the web service response string, and returns a structured data array
  public function decode($data) {
    return $data;
  }
}
