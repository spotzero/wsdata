<?php

namespace Drupal\wsdata_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'wsfield_text' formatter.
 *
 * @FieldFormatter(
 *   id = "wsfield_text",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "wsfield",
 *   }
 * )
 */
class WSFieldText extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $settings = $this->getFieldSettings();

    $elements = [];
    $options = [];

    $wscall = entity_load('wscall', $settings['wscall']);
    $data = $wscall->call('create');
    kint($data);
    /**
    $settings = $this->getFieldSettings();
    $wscall = entity_load('wscall', $settings['wscall']);
    $conn = $wscall->getMethods();
    kint($conn);

    $data = $wscall->call($options, 'create');

    kint($data);
    */

    $elements = array(
      '#markup' => 'hello',
    );
    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      kint($item->value);
      $elements[$delta] = [
        '#markup' => $item->value,
      ];
    }

    return $elements;
  }

}
