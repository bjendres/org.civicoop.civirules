<?php
/**
 * CiviRuleAction.Delete API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 */
function _civicrm_api3_civi_rule_action_delete_spec(&$spec) {
  $spec['id']['api_required'] = 0;
}

/**
 * CiviRuleAction.Delete API
 *
 * @param array $params
 *
 * @return array API result descriptor
 */
function civicrm_api3_civi_rule_action_delete($params) {
  return _civicrm_api3_basic_delete('CRM_Civirules_BAO_Action', $params);
}
