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
        'wsconfig_settings' => array(
          'wsconfig' => '',
          'arguments' => '',
          'options' => '',
        ),
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

    $form['settings']['wsconfig_settings'] = array(
      '#type' => 'fieldset',
      '#tree' => 1,
      '#title' => t('WsConfig Settings'),
    );

    $form['settings']['wsconfig_settings']['wsconfig'] = array(
      '#type' => 'select',
      '#title' => t('Web Service Config'),
      '#description' => t('The web service config to use to retrieve the data'),
      '#options' => wsconfig_get_list_by_name(),
      '#default_value' => isset($bean->settings['wsconfig']) ? $bean->settings['wsconfig'] : '',
    );

    foreach (wsconfig_get_list_by_name() as $machinename => $name) {
       $wsconfig = wsconfig_load_by_name($machinename);

       $items = array();
       foreach ($wsconfig->getOperations() as $opname) {
         $items[$opname] = $wsconfig->getMethodName($opname);
       }

      $form['settings']['wsconfig_settings']['wsconfig_method_' . $machinename] = array(
        '#type' => 'select',
        '#title' => t('Web Service Config Method'),
        '#description' => t('The web service method to use'),
        '#options' => $items,
        '#default_value' => isset($bean->settings['wsconfig_method']) ? $bean->settings['wsconfig_method'] : '',
        '#states' => array(
          'visible' => array(
            ':input[name="settings[wsconfig_settings][wsconfig]"]' => array('value' => $machinename),
          ),
        ),
      );
    }

    $form['settings']['wsconfig_settings']['arguments'] = array(
      '#type' => 'textfield',
      '#title' => t('Arguments'),
      '#description' => t('JSON encoded arguments to pass to the wsconfig'),
      '#default_value' => isset($bean->settings['arguments']) ? $bean->settings['arguments'] : '',
    );

    $form['settings']['wsconfig_settings']['options'] = array(
      '#type' => 'textfield',
      '#title' => t('Options'),
      '#description' => t('JSON encoded options'),
      '#default_value' => isset($bean->settings['options']) ? $bean->settings['options'] : '',
    );

    $form['settings']['wsprocessor'] = array(
      '#type' => 'select',
      '#title' => t('Web Service Processor'),
      '#description' => t('The web service processor to parse the data'),
      '#options' => wsconfig_get_form_processors(),
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
    if (!empty($bean->settings['themehook'])) {
      $form = array('#theme' => $bean->settings['themehook']);
    }

    $wsconfig = wsconfig_load_by_name($bean->settings['wsconfig_settings']['wsconfig']);

    if (!$wsconfig) {
      $content['bean'][$bean->delta]['form']['#markup'] = t('Unable to load WSConfig @wsconfig', array('@wsconfig' => $bean->settings['wsconfig']));
      return $content;
    }

    $processor = $bean->settings['wsprocessor'];
    if (!class_exists($processor) or !is_subclass_of($processor, 'WsData')) {
      $content['bean'][$bean->delta]['form']['#markup'] = t('Unable to load wsprocessor @wsprocessor', array('@wsprocessor' => $bean->settings['wsprocessor']));
      return $content;
    }

    $method = 'wsconfig_method_' . $bean->settings['wsconfig_settings']['wsconfig'];
    $arguments = json_decode($bean->settings['wsconfig_settings']['arguments']);
    $arguments = isset($arguments) ? $arguments : array();

    $options = json_decode($bean->settings['wsconfig_settings']['options']);
    $options = isset($options) ? $options : array();

    $data = $wsconfig->call(
      $bean->settings['wsconfig_settings'][$method],
      array(),
      $arguments,
      $options
   );

    // Create a new processor
    $processor = new $processor($data);

    foreach ($processor->data as $key => $val) {
      if (is_scalar($val)) {
        $form['#' . $key] = $val;
      } else {
        $form[$key] = $val;
      }
    }

    $content['bean'][$bean->delta]['form'] = $form;
    return $content;
  }
}
