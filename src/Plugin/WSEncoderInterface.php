<?php

namespace Drupal\wsdata\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Web Service Encoder plugins.
 */
interface WSEncoderInterface extends PluginInspectionInterface {
  public function encode(&$data, &$replacement, &$url);
}
