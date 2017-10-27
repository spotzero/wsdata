<?php

namespace Drupal\wsdata_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wsdata\Plugin\WSEncoderManager;
use Drupal\wsdata\Plugin\WSDecoderManager;


/**
 * Defines the 'wsfield' field type.
 *
 * @FieldType(
 *   id = "wsfield",
 *   label = @Translation("Web service field"),
 *   description = @Translation("WSdata field field type."),
 *   category = @Translation("General"),
 *   default_widget = "wsfield",
 *   default_formatter = "wsfield_text"
 * )
 */
class WSFieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return array(
      'custom_storage' => TRUE,
    ) + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return array(
      'wscall' => '',
      'webservice_value' => '',
    ) + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['summary'] = DataDefinition::create('string')
      ->setLabel(t('Summary'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'text',
          'size' => 'big',
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('summary')->getValue();
    return parent::isEmpty() && ($value === NULL || $value === '');
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = array();
    $settings = $this->getSettings();

    $wscalls = entity_load_multiple('wscall');
    $options = [];
    foreach ($wscalls as $wscall) {
      $options[$wscall->id()] = $wscall->label();
    }

    $element['wscall'] = array(
      '#type' => 'select',
      '#title' => t('Wscall'),
      '#default_value' => $settings['wscall'],
      '#options' => $options,
    );

    $element['webservice_value'] = array(
      '#type' => 'textfield',
      '#title' => t('Webservice value return value to fetch'),
      '#default_value' => $settings['webservice_value'],
      '#description' => t('Insert the token of the item you wish to be displayed from the return of the webservice.'),
    );

    return $element;
  }
}
