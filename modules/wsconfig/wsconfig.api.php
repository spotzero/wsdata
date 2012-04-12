<?php

/**
 * @file
 * Describe the file
 *
 * @author Mathew Winstone <mwinstone@coldfrontlabs.ca>
 * @copyright 2011 Coldfront Labs Inc.
 * @license Copyright (c) 2011 All rights reserved
 */

/**
 * Register as a connector module
 *
 * @return array
 *  Returns an array definining the function callbacks for all supported
 *  CRUD operations
 */
function hook_wsconfig_connector_info() {
  // Array of functions to call for each CRUD task
  
  // Since modules can create multiple connectors, add a unique ID to define
  // each set of CRUD operations. Typically, this can be your module name if
  // you only implement one connector. Otherwise, adding mymodule_rest or
  // mymodule_soap is another appropriate naming convention
  
  // The array should contain at minimum one CRUD operation and the human
  // readable name of your connector. Do NOT wrap the name in the t() function
  // since it will be translated automatically later on.
  
  // The keys in the array must match the following:
  return array(
    'myconnectorid' => array(
      'name' => 'Display Name for MyConnector',
      'class' => 'myprocessorclassname',
    ),
  );
}

/**
 * Define a list of processors your module implements
 *
 * Processors are used to parse the data coming back from web service calls
 * into the proper format for storing data in fields. They are responsible
 * for creating the structure arrays for all supported entities/fields.
 *
 * This hook allows modules to register classes which implement the WsData
 * class and allows the user to select the appropriate processor through
 * the UI.
 *
 * @return array
 *  Returns a structure array defining your processor(s)
 *
 *  Since the processor classes may implement fields and/or entities, the
 *  hook requires implementations to register all the supported objects.
 *
 *  Ex: array('MyModuleWsDataJson' => array('text' => 'JSON Parser for My Module'));
 */
function hook_wsconfig_processor_info() {
  return array(
    'myprocessorclassname' => array(
      'fields' => array(
        'machinetype' => 'Display name for MyProcessor',
      ),
      'entities' => array(
        'machinetype' => 'Display name for MyProcessor',
      ),
    ),
  );
}

/**
 * Sample implementation of WsData
 *
 * This class is responsible for all data functions on your web service data.
 * It parses the data from the service call and exports it in the appropriate
 * format. For example, you can have a processor for json data and have it
 * output the data in formatted arrays to be used with fields.
 */
class SampleProcessor extends WsData {
  // @todo
}

/**
 * Sample implementation of WsConnector
 *
 * This class is responsible for handling the web service requests for all
 * CRUD operations. The class can contain all functionality for making a given
 * request (i.e. http request) or is can wrap another set of functions into
 * it (ex: restclient).
 */
class SampleConnector extends WsConnector {
  // @todo
}