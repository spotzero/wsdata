<?php
/**
 * @file
 * Listing bean plugin.
 */

class WsBean extends BeanPlugin {

  /**
   * Default values for the bean's settings
   */
  public function values() {
    $values = array(
      'settings' => array(
        'wsconfig' => '',
        'wsprocessor' => '',
        'themehook' => '',
      ),
    );

    return array_merge(parent::values(), $values);
  }

    /**
   * Builds extra settings for the block edit form.
   */
  public function form($bean, $form, &$form_state) {
    $form = array();
    $form['settings'] = array(
      '#type' => 'fieldset',
      '#tree' => 1,
      '#title' => t('WsBean Settings'),
      '#description' => t('To render content into a wsbean, the data is retrived by the selected Web Service Configure, then if is parsed by the Web Service Processor, the result of that is then passed to the given theme hook to be rendered.'),
    );

    $form['settings']['wsconfig'] = array(
      '#type' => 'select',
      '#title' => t('Web Service Config'),
      '#description' => t('The web service config to use to retrieve the data'),
      '#options' => wsconfig_get_list_by_name(),
      '#default_value' => isset($bean->settings['wsconfig']) ? $bean->settings['wsconfig'] : '',
    );

    $form['settings']['wsprocessor'] = array(
      '#type' => 'select',
      '#title' => t('Web Service Processor'),
      '#description' => t('The web service processor to parse the data'),
      '#options' => wsconfig_get_field_processors(),
      '#default_value' => isset($bean->settings['wsprocessor']) ? $bean->settings['wsprocessor'] : '',
    );

    $form['settings']['themehook'] = array(
      '#type' => 'textfield',
      '#title' => t('Theme Hook'),
      '#description' => t('The name of the theme hook to render the data received from the processor with.'),
      '#default_value' => isset($bean->settings['themehook']) ? $bean->settings['themehook'] : '',
    );
    return $form;
  }

  /**
   * Displays the bean.
   */
  public function view($bean, $content, $view_mode = 'default', $langcode = NULL) {
    return $content;
  }
}
