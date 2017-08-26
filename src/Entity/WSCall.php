<?php

namespace Drupal\wsdata\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Web Service Call entity.
 *
 * @ConfigEntityType(
 *   id = "wscall",
 *   label = @Translation("Web Service Call"),
 *   handlers = {
 *     "list_builder" = "Drupal\wsdata\WSCallListBuilder",
 *     "view_builder" = "Drupal\wsdata\WSCallViewBuilder",
 *     "form" = {
 *       "add" = "Drupal\wsdata\Form\WSCallForm",
 *       "edit" = "Drupal\wsdata\Form\WSCallForm",
 *       "delete" = "Drupal\wsdata\Form\WSCallDeleteForm",
 *       "test" = "Drupal\wsdata\Form\WSCallTestForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\wsdata\WSCallHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "wscall",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/wscall/{wscall}",
 *     "add-form" = "/admin/structure/wscall/add",
 *     "edit-form" = "/admin/structure/wscall/{wscall}/edit",
 *     "delete-form" = "/admin/structure/wscall/{wscall}/delete",
 *     "collection" = "/admin/structure/wscall"
 *   }
 * )
 */
class WSCall extends ConfigEntityBase implements WSCallInterface {
  /**
   * The Web Service Call ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Web Service Call label.
   *
   * @var string
   */
  protected $label;

  public $options;

  public $wsserver;
  public $wsdecoder;

  protected $wsserverInst;
  protected $wsdecoderInst;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    $this->wsserverInst = entity_load('wsserver', $this->wsserver);
    $wsdecoderManager = \Drupal::service('plugin.manager.wsdecoder');
    $wspdefs = $wsdecoderManager->getDefinitions();
    if (isset($wspdefs[$this->wsdecoder])) {
      $this->wsdecoderInst = $wsdecoderManager->createInstance($this->wsdecoder);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setEndpoint($endpoint) {
    $this->wsserverInst->setEndpoint($endpoint);
  }

  /**
   * {@inheritdoc}
   */
  public function getEndpoint() {
    return $this->wsserverInst->getEndpoint();
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguagePlugin() {}

  /**
   * {@inheritdoc}
   */
  public function call($type, $key = NULL, $replacement = [], $argument = [], $options = [], &$method = '') {}

  /**
   * {@inheritdoc}
   *
   * Doesn't save the WSCall though.
   */
  public function setOptions($values = []) {
    if ($this->wsserverInst->wsconnectorInst) {
      $this->options[$this->wsserver] = $this->wsserverInst->wsconnectorInst->saveOptions($values);
    }
    $this->needSave = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getReplacements() {
    return $this->wsserverInst->wsconnectorInst->getReplacements($this->getOptions());
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionsForm($wsserver = NULL, $options = []) {
    if (isset($wsserver)) {
      $wsserverInst = entity_load('wsserver', $wsserver);
      return $wsserverInst->wsconnectorInst->getOptionsForm();
    }
    if ($this->wsserverInst->wsconnectorInst) {
      return $this->wsserverInst->wsconnectorInst->getOptionsForm();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return isset($this->options[$this->wsserver]) ? $this->options[$this->wsserver] : array();
  }

  /**
   * {@inheritdoc}
   */
  public function getMethods() {
    return $this->wsserverInst->getMethods();
  }

  /**
   * {@inheritdoc}
   */
  public function addData($data) {
    if (!isset($this->wsdecoderInst)) {
      $wsdecoderManager = \Drupal::service('plugin.manager.wsdecoder');
      $this->wsdecoderInst = $wsdecoderManager->createInstance($this->wsdecoder);
    }
    return $this->wsdecoderInst->addData($data);
  }

  /**
   * {@inheritdoc}
   */
  public function getData($key = NULL) {
    if (isset($this->wsdecoderInst)) {
      return $this->wsdecoderInst->getData($key);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getConnector() {
    return $this->wsserverInst->getConnector();
  }

}
