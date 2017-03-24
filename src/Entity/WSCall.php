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
 *       "delete" = "Drupal\wsdata\Form\WSCallDeleteForm"
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
  public $wsparser;

  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    $this->wsserverInst = entity_load('wsserver', $this->wsserver);
    $wsparserManager = \Drupal::service('plugin.manager.wsparser');
    $wspdefs = $wsparserManager->getDefinitions();
  	if (isset($wspdefs[$this->wsparser])) {
      $this->wspaserInst = $wsparserManager->createInstance($this->wsparser);
  	}
  }

  public function setEndpoint($endpoint) {
    $this->wsserverInst->setEndpoint($endpoint);
  }

  public function getEndpoint() {
    return $this->wsserverInst->getEndpoint();
  }

  public function getLanguagePlugin() {}
  public function call($type, $key = NULL, $replacement = array(), $argument = array(), $options = array(), &$method = '') {}

  /**
   * Sets a method on the WSCall.
   * Doesn't save the WSCall though.
   */
  public function setOptions($values = array()) {
    if ($this->wsserverInst->wsconnectorInst) {
      $this->options[$this->wsserver] = $this->wsserverInst->wsconnectorInst->saveOptions($values);
    }
    $this->needSave = TRUE;
  }

  public function getReplacements($type, $replacement = array()) {}

  public function getOptionsForm() {
    if ($this->wsserverInst->wsconnectorInst) {
      return $this->wsserverInst->wsconnectorInst->getOptionsForm();
    }
  }

  public function getOptions() {
    return $this->options[$this->wsserver];
  }

  public function getMethods() {
    return $this->wsserverInst->getMethods();
  }
}
