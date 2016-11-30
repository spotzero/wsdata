<?php

namespace Drupal\wsdata\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Web Service Server entity.
 *
 * @ConfigEntityType(
 *   id = "wsserver",
 *   label = @Translation("Web Service Server"),
 *   handlers = {
 *     "list_builder" = "Drupal\wsdata\WSServerListBuilder",
 *     "form" = {
 *       "add" = "Drupal\wsdata\Form\WSServerForm",
 *       "edit" = "Drupal\wsdata\Form\WSServerForm",
 *       "delete" = "Drupal\wsdata\Form\WSServerDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\wsdata\WSServerHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "wsserver",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/wsserver/{wsserver}",
 *     "add-form" = "/admin/structure/wsserver/add",
 *     "edit-form" = "/admin/structure/wsserver/{wsserver}/edit",
 *     "delete-form" = "/admin/structure/wsserver/{wsserver}/delete",
 *     "collection" = "/admin/structure/wsserver"
 *   }
 * )
 */
class WSServer extends ConfigEntityBase implements WSServerInterface {

  /**
   * The Web Service Server ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Web Service Server label.
   *
   * @var string
   */
  protected $label;

}
