<?php

namespace Drupal\wsdata\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Wsdecoder plugin plugins.
 */
interface WSDecoderInterface extends PluginInspectionInterface {
  public function accepts();
  public function isCachable();
  public function getError();
  public function getData($key = NULL, $lang = NULL);
  public function addData($data, $lang = NULL, $context = []);
}
