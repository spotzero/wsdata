<?php

namespace Drupal\wsdata\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Wsconnector plugin plugins.
 */
abstract class WSConnectorBase extends PluginBase implements WSConnectorPluginInterface {
  protected $expires;
  protected $cacheDefaultTime;
  protected $cacheDefaultOverride;
  protected $staleCache;
  protected $endpoint;
  protected $error;

  // All connectors support the default plugin by default.
  // The plugin essentially means all the language data is
  // in the single request.
  protected $languagePlugins = array('default');

  abstract function getMethods();

  abstract public function wscall($type, $method, $argument, $options);

  public function __construct($endpoint) {
    $this->endpoint = trim($endpoint);
    $this->expires = 0;
    $this->cacheDefaultTime = 0;
    $this->cacheDefaultOverride = FALSE;
    $this->staleCache = FALSE;
  }

  public function getEndpoint() {
    return $this->endpoint;
  }

  public function supportsCaching() {
    return FALSE;
  }

  public function getError() {
    return $this->error;
  }

  /**
   * Return the list of supported language handling plugins
   */
  public function getSupportedLanguagePlugins() {
    return $this->languagePlugins;
  }

  public function defaultCache($mintime = 0, $override = FALSE, $stale = FALSE) {
    $this->cacheDefaultTime = $mintime;
    $this->cacheDefaultOverride = $override;
    $this->staleCache = $stale;
  }

  public function expires() {
    if ($this->expires > 0) {
      return $this->expires;
    }
    else {
      return FALSE;
    }
  }

  public function isDegraded() {
    return FALSE;
  }

  protected function setError($code, $message) {
    $this->error = array(
      'code' => $code,
      'message' => $message,
    );
  }

  protected function clearError() {
    $this->error = NULL;
  }
}
