<?php

/**
 * @file
 * Entity classes
 */

/**
 * The class used for wsconfig entities
 */
class WsConfig extends Entity {

  public $connector;

  public function __construct($values = array()) {
    parent::__construct($values, 'wsconfig');
    $type = wsconfig_type_load($this->type);

    if (isset($type->data['connector']) and class_exists($type->data['connector'])) {
      $this->connector = new $type->data['connector']($type->data['endpoint']);
    }
  }

  public function call($type, $replacement = array(), $argument = array(), $options = array(), &$method = '') {
    $method = $this->getMethod($type, $replacement);
    return $this->connector->wscall($type, $method, $argument, $options);
  }

  public function getMethod($type, $replacement = array()) {
    if (!isset($this->data[$type . '_data_method'])) {
      return FALSE;
    }
    $method = $this->data[$type . '_data_method'];
    foreach ($replacement as $token => $replace) {
       $method = str_replace( $token, $replace, $method);
    }
    return $method;
  }

  public function getOperations() {
    $ops = array();
    foreach ($this->data as $key => $val) {
      if (drupal_substr($key, -1 * drupal_strlen('_data_method')) == '_data_method') {
         $ops[] = drupal_substr($key, 0, -1 * drupal_strlen('_data_method'));
      }
    }
    if (empty($ops)) {
      return array();
    }
    return $ops;
  }

/*
  public function isNamedOperation($operation) {
    $supported = $this->connector->getMethods();
    foreach ($supported['multiple'] as $key => $val) {
      if (drupal_substr($operation, 0, drupal_strlen($key) + 1) == $key . '_') {
         return TRUE;
      }
    }
    return FALSE;
  }
*/

  public function addMethod($type, $name = '') {
    $methods = $this->getPossibleMethods();
    if (!isset($methods[$type])) {
      return FALSE;
    }

    $methodname = $type;

    $supported = $this->connector->getMethods();
    if (isset($supported['multiple'][$type])) {
      $name = drupal_strtolower(preg_replace('/\W/', '', $name));
      if (empty($name)) {
        return FALSE;
      }

      $methodname .= '_' . $name;
    }

    $this->data[$this->getMethodKey($methodname)] = '';
    return TRUE;
  }

  public function getPossibleMethods() {
    $supported = $this->connector->getMethods();
    $methods = array_merge($supported['single'], $supported['multiple']);
    foreach ($this->getOperations() as $op) {
      if (isset($supported['single'][$op])) {
        unset($methods[$op]);
      }
    }
    return $methods;
  }

  public function getMethodKey($operation) {
    return $operation . '_data_method';
  }

  public function getMethodName($operation) {
    $supported = $this->connector->getMethods();
    foreach ($supported['multiple'] as $key => $val) {
      if (drupal_substr($operation, 0, drupal_strlen($key) + 1) == $key . '_') {
         $operation = ucfirst($key) . ': ' . ucfirst(drupal_substr($operation, drupal_strlen($key) + 1));
         return $operation;
      }
    }
    return ucfirst($operation);
  }

  protected function defaultLabel() {
    return $this->title;
  }

  protected function defaultUri() {
    return array('path' => 'wsconfig/' . $this->name);
  }
}

/**
 * The Controller for WsConfig entities
 */
class WsConfigController extends EntityAPIControllerExportable {
  public function __construct($entityType) {
    parent::__construct($entityType);
  }

  /**
   * Create a wsconfig - we first set up the values that are specific
   * to our wsconfig schema but then also go through the EntityAPIController
   * function.
   *
   * @param $type
   *   The machine-readable type of the wsconfig.
   *
   * @return
   *   A wsconfig object with all default fields initialized.
   */
  public function create(array $values = array()) {
    // Add values that are specific to our WsConfig
    $values += array(
      'wsconfig_id' => '',
      'name' => '',
      'is_new' => TRUE,
      'title' => '',
      'created' => '',
      'changed' => '',
      'data' => array(),
    );

    $wsconfig = parent::create($values);
    return $wsconfig;
  }

  /**
   * Overriding the buildContent function to add entity specific fields
   */
  public function buildContent($entity, $view_mode = 'full', $langcode = NULL, $content = array()) {
    $content = parent::buildContent($entity, $view_mode, $langcode, $content);
//    $content['wsconfig_sample_data'] =  array(
//      '#markup' => theme('wsconfig_sample_data', array('wsconfig_sample_data' => check_plain($entity->data['sample_data']), 'wsconfig' => $entity)),
//    );

    return $content;
  }
}

/**
 * UI controller
 */
class WsConfigUIController extends EntityDefaultUIController {
  /**
   * Overrides hook_menu() defaults. Main reason for doing this is that
   * parent class hook_menu() is optimized for entity type administration.
   */
  public function hook_menu() {
    $items = parent::hook_menu();
    $id_count = count(explode('/', $this->path));
    $wildcard = isset($this->entityInfo['admin ui']['menu wildcard']) ? $this->entityInfo['admin ui']['menu wildcard'] : '%' . $this->entityType;

    $items[$this->path] = array(
      'title' => 'Web Service Configurations',
      'description' => 'Add edit and update web service configurations.',
      'page callback' => 'system_admin_menu_block_page',
      'access arguments' => array('access administration pages'),
      'file path' => drupal_get_path('module', 'system'),
      'file' => 'system.admin.inc',
    );

    // Change the overview menu type for the list of web service configurations.
    $items[$this->path]['type'] = MENU_LOCAL_TASK;

    // Change the add page menu to multiple types of entities
    $items[$this->path . '/add'] = array(
      'title' => 'Add a wsconfig',
      'description' => 'Add a new wsconfig',
      'page callback'  => 'wsconfig_add_page',
      'access callback'  => 'wsconfig_access',
      'access arguments' => array('edit'),
      'type' => MENU_NORMAL_ITEM,
      'weight' => 20,
      'file' => 'wsconfig.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module'])
    );

    // Add menu items to add each different type of entity.
    foreach (wsconfig_get_types() as $type) {
      $items[$this->path . '/add/' . $type->type] = array(
        'title' => 'Add ' . $type->label,
        'page callback' => 'wsconfig_form_wrapper',
        'page arguments' => array(wsconfig_create(array('type' => $type->type))),
        'access callback' => 'wsconfig_access',
        'access arguments' => array('edit', 'edit ' . $type->type),
        'file' => 'wsconfig.admin.inc',
        'file path' => drupal_get_path('module', $this->entityInfo['module'])
      );
    }

    // Loading and editing wsconfig entities
    $items[$this->path . '/wsconfig/' . $wildcard] = array(
      'page callback' => 'wsconfig_form_wrapper',
      'page arguments' => array($id_count + 1),
      'access callback' => 'wsconfig_access',
      'access arguments' => array('edit', $id_count + 1),
      'weight' => 0,
      'context' => MENU_CONTEXT_PAGE | MENU_CONTEXT_INLINE,
      'file' => 'wsconfig.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module'])
    );

    $items[$this->path . '/wsconfig/' . $wildcard . '/edit'] = array(
      'title' => t('Edit'),
      'type' => MENU_DEFAULT_LOCAL_TASK,
      'weight' => -10,
      'context' => MENU_CONTEXT_PAGE | MENU_CONTEXT_INLINE,
    );

    $items[$this->path . '/wsconfig/' . $wildcard . '/delete'] = array(
      'title' => t('Delete'),
      'page callback' => 'wsconfig_delete_form_wrapper',
      'page arguments' => array($id_count + 1),
      'access callback' => 'wsconfig_access',
      'access arguments' => array('edit', $id_count + 1),
      'type' => MENU_LOCAL_TASK,
      'context' => MENU_CONTEXT_INLINE,
      'weight' => 10,
      'file' => 'wsconfig.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module'])
    );

    // Menu item for viewing web service configurations
    $items['wsconfig/' . $wildcard] = array(
      //'title' => 'Title',
      'title callback' => 'wsconfig_page_title',
      'title arguments' => array(1),
      'page callback' => 'wsconfig_view_form_wrapper',
      'page arguments' => array(1),
      'access callback' => 'wsconfig_access',
      'access arguments' => array('view', 1),
      'type' => MENU_CALLBACK,
      'file' => 'wsconfig.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module']),
    );
    return $items;
  }


  /**
   * Create the markup for the add Web Service Configuration Entities page within the class
   * so it can easily be extended/overriden.
   */
  public function addPage() {
    $item = menu_get_item();
    $content = system_admin_menu_block($item);

    if (count($content) == 1) {
      $item = array_shift($content);
      drupal_goto($item['href']);
    }

    return theme('wsconfig_add_list', array('content' => $content));
  }
}


/**
 * The class used for wsconfig type entities
 */
class WsConfigType extends Entity {
  public $type;
  public $label;

  public function __construct($values = array()) {
    parent::__construct($values, 'wsconfig_type');
  }
}


/**
 * The Controller for wsconfig type entities
 */
class WsConfigTypeController extends EntityAPIControllerExportable {
  public function __construct($entityType) {
    parent::__construct($entityType);
  }

  /**
   * Create a wsconfig type - we first set up the values that are specific
   * to our wsconfig type schema but then also go through the EntityAPIController
   * function.
   *
   * @param $type
   *   The machine-readable type of the wsconfig.
   *
   * @return
   *   A wsconfig type object with all default fields initialized.
   */
  public function create(array $values = array()) {
    // Add values that are specific to our Web Service Configuration
    $values += array(
      'id' => '',
      'is_new' => TRUE,
      'data' => array(),
    );
    $wsconfig_type = parent::create($values);
    return $wsconfig_type;
  }
}

/**
 * UI controller
 */
class WsConfigTypeUIController extends EntityDefaultUIController {
  /**
   * Overrides hook_menu() defaults.
   */
  public function hook_menu() {
    $items = parent::hook_menu();
		$items[$this->path]['description'] = 'Manage wsconfig entity types, including adding and removing fields and the display of fields.';
    return $items;
  }
}
