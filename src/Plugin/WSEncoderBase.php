<?php

namespace Drupal\wsdata\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Web Service Encoder plugins.
 */
abstract class WSEncoderBase extends PluginBase implements WSEncoderInterface {

  /**
   * Encode data into format just sending it off.
   */
  abstract public function encode(&$data, &$replacement, &$url);

  /**
   * Returns whether or not the result on the encoder are cacheable.
   */
  public function isCacheable() {
    return TRUE;
  }

}
