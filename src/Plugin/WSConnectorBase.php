<?php

namespace Drupal\wsdata\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Wsconnector plugin plugins.
 */
abstract class WSConnectorBase extends PluginBase implements WSConnectorInterface {
  protected $expires;
  protected $cacheDefaultTime;
  protected $cacheDefaultOverride;
  protected $staleCache;
  protected $endpoint;
  protected $error;

  protected $languagePlugins = ['default'];

  /**
   * Return available options supported by the connector.
   */
  abstract public function getOptions();

  /**
   * Return available methods supported by the connector.
   */
  abstract public function getMethods();

  /**
   * Return the settings form provided by the connector.
   */
  public function getOptionsForm() {
    return [];
  }

  /**
   * Make the connector call.
   */
  abstract public function call($options, $method, $replacements = [], $data = NULL);

  /**
   * Constructor.
   */
  public function __construct() {
    $this->expires = 0;
    $this->cacheDefaultTime = 0;
    $this->cacheDefaultOverride = FALSE;
    $this->staleCache = FALSE;
  }

  /**
   * Setter for the endpoint.
   */
  public function setEndpoint($endpoint) {
    $this->endpoint = trim($endpoint);
  }

  /**
   * Getter for the endpoint.
   */
  public function getEndpoint() {
    return $this->endpoint;
  }

  /**
   * Whether returned data can be cached.
   */
  public function supportsCaching() {
    return FALSE;
  }

  /**
   * Return the last error the connector received.
   */
  public function getError() {
    return $this->error;
  }

  /**
   * Return the list of supported language handling plugins.
   */
  public function getSupportedLanguagePlugins() {
    return $this->languagePlugins;
  }

  /**
   * Figure out the overrides for cache times.
   */
  public function defaultCache($mintime = 0, $override = FALSE, $stale = FALSE) {
    $this->cacheDefaultTime = $mintime;
    $this->cacheDefaultOverride = $override;
    $this->staleCache = $stale;
  }

  /**
   * Get the expired time for caching.
   */
  public function expires() {
    if ($this->expires > 0) {
      return $this->expires;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Whether the connector is in a dead state and shouldn't be called.
   */
  public function isDegraded() {
    return FALSE;
  }

  /**
   * Setter for the connector errors.
   */
  protected function setError($code, $message) {
    $this->error = [
      'code' => $code,
      'message' => $message,
    ];
  }

  /**
   * Clear current error.
   */
  protected function clearError() {
    $this->error = NULL;
  }

  /**
   * Saves the options form.
   */
  public function saveOptions($values) {
    $options = [];

    foreach (array_keys($this->getOptionsForm()) as $option) {
      $options[$option] = $values[$option];
    }
    return $options;
  }

}
