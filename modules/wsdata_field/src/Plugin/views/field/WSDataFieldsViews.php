<?php

namespace Drupal\wsdata_field\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler for the wsdata fields module.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("wsdata_field_views")
 */
class WSDataFieldsViews extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $return = '';
    // Based on this field check if the node type has this field.
    $field_name = $this->field;
    $entity = $values->_entity;
    if ($entity->hasField($field_name)) {
      // Load the wsfield config entity.
      $wsfield_config = entity_load('wsfield_config', $field_name);

      // Get the replacements.
      $replacements = is_array($wsfield_config->replacements) ? $wsfield_config->replacements : [];
      $wsdata = \Drupal::service('wsdata');
      // Create the call based on the wsfield configurations.
      $return = $wsdata->call(
        $wsfield_config->wscall,
        NULL,
        $replacements,
        $wsfield_config->data,
        [
          'langcode' => $entity->language()->getId(),
        ],
        $wsfield_config->returnToken,
        [
          $entity->getEntityTypeId() => $entity,
        ]
      );
    }
    return $return;
  }

}
