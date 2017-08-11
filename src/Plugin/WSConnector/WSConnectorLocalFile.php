<?php

namespace Drupal\wsdata\Plugin\WSConnector;

use Drupal\wsdata\Plugin\WSConnectorBase;


/**
 * Local file connector.
 *
 * @WSConnector(
 *   id = "WSConnectorLocalFile",
 *   label = @Translation("Local file connector", context = "WSConnector"),
 * )
 */
class WSConnectorLocalFile extends WSConnectorBase {
  public function __construct($endpoint) {
    $this->languagePlugins = array(
      'replace',
    );
    parent::__construct($endpoint);
  }

  public function getMethods() {
    return array('read', 'write', 'append');
  }

  public function getOptions() {
    return array(
      'filename' => NULL,
      'readonly' => TRUE,
    );
  }

  public function getOptionsForm() {
    return array(
      'filename' => array(
        '#title' => t('Filename'),
        '#type' => 'textfield',
      ),
      'readonly' => array(
        '#title' => t('Prevent writing to this file.'),
        '#type' => 'checkbox',
      ),
    );
  }

  public function call($options, $method, $replacements = [], $data = NULL) {
    $filename = $this->endpoint . '/' . $options['filename'];
    $flags = 0;
    switch ($method) {
      case 'append':
        $flags = FILE_APPEND;
      case 'write':
        if (!is_writable($filename)) {
          $this->setError(1, t("$filename is not writable."));
          return FALSE;
        }
        return file_put_contents($filename, $data, $flags);

        case 'read':
        default:
          if (!file_exists($filename)) {
            $this->setError(1, t("$filename does not exist."));
            return FALSE;
          }
          if (!is_readable($filename)) {
            $this->setError(1, t("$filename is not readable."));
            return FALSE;
          }
          return file_get_contents($filename);
    }
  }
}
