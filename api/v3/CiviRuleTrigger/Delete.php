<?php

/**
 * CiviRuleTrigger.Delete API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_civi_rule_trigger_delete_spec(&$spec) {
  $spec['id']['api_required'] = 1;
}

/**
 * CiviRuleTrigger.Delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civi_rule_trigger_delete($params) {
  return _civicrm_api3_basic_delete('CRM_Civirules_BAO_Trigger', $params);
}

