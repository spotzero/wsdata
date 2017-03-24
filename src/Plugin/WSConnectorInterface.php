<?php

namespace Drupal\wsdata\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Wsconnector plugin plugins.
 */
interface WSConnectorInterface extends PluginInspectionInterface {

  public function getOptionsForm();
  // Add get/set methods for your plugin type here.

}
