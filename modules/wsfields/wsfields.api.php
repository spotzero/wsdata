<?php

/**
 * @file
 * API Documentation for wsfields
 *
 * @author David Pascoe-Deslauriers <dpascoed@coldfrontlabs.ca>
 * @author Mathew Winstone <mwinstone@coldfrontlabs.ca>
 * @copyright 2011 Coldfront Labs Inc.
 * @license Copyright (c) 2011 All rights reserved
 */

/**
 * Allows modules to alter and format field data prior to it being added to
 * a field instance.
 *
 * @param array $instance
 *  Instance of a field
 * @return array
 *  Returns a formatted array for use in a field instance
 */
function hook_wsfields_FIELD_TYPE_data_alter($data, $instance) {
  
}