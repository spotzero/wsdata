<?php

namespace Drupal\wsdata;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides a WSCall view builder.
 */
class WSCallViewBuilder extends EntityViewBuilder {
  public function viewMultiple(array $entities = array(), $view_mode = 'full', $langcode = NULL) {
    dpm($entities);
    return parent::viewMultiple($entities, $view_mode, $langcode);
  }
  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    parent::buildComponents($build, $entities, $displays, $view_mode);

    foreach ($entities as $id => $entity) {
      $build[$id]['label'] = array(
        '#weight' => -100,
        '#plain_text' => $entity->label(),
      );
      $build[$id]['separator'] = array(
        '#weight' => -150,
        '#markup' => ' | ',
      );
      $build[$id]['view_mode'] = array(
        '#weight' => -200,
        '#plain_text' => $view_mode,
      );
    }
  }
}
