<?php

namespace Drupal\wsdata_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Plugin implementation of the 'Wsfield' widget.
 *
 * @FieldWidget(
 *   id = "wsfield",
 *   label = @Translation("WSField"),
 *   field_types = {
 *     "wsfield"
 *   }
 * )
 */
class WSFieldWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['value'] = $element;
    return $element;
  }

}
