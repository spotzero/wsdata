<?php

namespace Drupal\wsdata\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Web Service Encoder plugins.
 */
interface WSEncoderInterface extends PluginInspectionInterface {

  /**
   * Modify the given data before the call.
   */
  public function encode(&$data, &$replacement, &$url);

}
