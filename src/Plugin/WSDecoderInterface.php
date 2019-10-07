<?php

namespace Drupal\wsdata\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Wsdecoder plugin plugins.
 */
interface WSDecoderInterface extends PluginInspectionInterface {

  /**
   * Returns a list of the data/encoding formats support by this decoder.
   */
  public function accepts();

  /**
   * Returns the error from the last parse the decoder ran.
   */
  public function getError();

  /**
   * Return the data element at the location given.
   */
  public function getData($key = NULL, $lang = NULL);

  /**
   * Add raw data to be parsed.
   */
  public function addData($data, $lang = NULL, $context = []);

}
