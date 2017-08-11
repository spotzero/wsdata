<?php

namespace Drupal\wsdata\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Wsconnector plugin plugins.
 */
interface WSConnectorInterface extends PluginInspectionInterface {

  /**
   * Return the custom settings form for the wscall page.
   */
  public function getOptionsForm();

}
