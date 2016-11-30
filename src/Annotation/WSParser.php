<?php

namespace Drupal\wsdata\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Wsparser plugin item annotation object.
 *
 * @see \Drupal\wsdata\Plugin\WSParserPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class WSParser extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
