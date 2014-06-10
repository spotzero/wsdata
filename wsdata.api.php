<?php
/**
 * API Documentation for WSData
 */

/**
 * List of language handler plugins
 *
 * Adds to the list of available language handling plugins.
 * The plugins do nothing on their own. They simply define
 * a set of functionality which the WsConnector implementations
 * can reference.
 *
 * Language plugins define how multilingual data is requested
 * from a web service. There are two classes of plugins:
 *
 * 1. Single request plugins
 * 2. Multi request plugins
 *
 * Single request plugins mean that all language data is captured
 * in a single request. Usually this means the data processor
 * is responsible for parsing keyed values in the return data
 * per language. This is the default language handler.
 *
 * Multi request handlers imply that multiple web service requests
 * are required to capture all languages available. The default
 * methods defined for this are "header", "argument", "path" and "uri".
 * It is the responsibility of the WsConnector to support these plugins.
 *
 * You may define your own plugins if you have a particularly unique
 * language request mechanism (ex: post body data)
 *
 * For an example implementation
 * @see http://drupal.org/project/restclient
 */
function hook_wsdata_language_plugin() {
  return array(
    'header',
    'argument',
    'path',
    'uri',
    'default',
  );
}