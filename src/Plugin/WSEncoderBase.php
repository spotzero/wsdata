<?php

namespace Drupal\wsdata\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Web Service Encoder plugins.
 */
abstract class WSEncoderBase extends PluginBase implements WSEncoderInterface {


  // Add common methods and abstract methods for your plugin type here.

  abstract function encode( &$data, &$replacement, &$url);
}
