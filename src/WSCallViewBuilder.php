<?php

namespace Drupal\wsdata;

use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Provides a WSCall view builder.
 */
class WSCallViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function viewMultiple(array $entities = [], $view_mode = 'full', $langcode = NULL) {
    // @TODO: Load linked configuration for display.
    $todo = 'update this view';
    return parent::viewMultiple($entities, $view_mode, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    parent::buildComponents($build, $entities, $displays, $view_mode);

    foreach ($entities as $id => $entity) {
      $build[$id]['label'] = [
        '#weight' => -100,
        '#plain_text' => $entity->label(),
      ];
      $build[$id]['separator'] = [
        '#weight' => -150,
        '#markup' => ' | ',
      ];
      $build[$id]['view_mode'] = [
        '#weight' => -200,
        '#plain_text' => $view_mode,
      ];
    }
  }

}
