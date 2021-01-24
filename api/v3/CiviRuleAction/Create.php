<?php
/**
 * CiviRuleAction.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 */
function _civicrm_api3_civi_rule_action_create_spec(&$spec) {
  $spec['label']['api_required'] = 0;
  $spec['name']['api_required'] = 0;
  $spec['class_name']['api_required'] = 0;
}

/**
 * CiviRuleAction.Create API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 */
function civicrm_api3_civi_rule_action_create($params) {
  if (!isset($params['id']) && empty($params['label'])) {
    return civicrm_api3_create_error('Label can not be empty when adding a new CiviRule Action');
  }
  if (empty($params['class_name']) && !isset($params['id'])) {
    return civicrm_api3_create_error('Class_name can not be empty');
  }
  /*
   * set created or modified date and user_id
   */
  $userId = CRM_Core_Session::getLoggedInContactID();
  if (isset($params['id'])) {
    $params['modified_date'] = date('Ymd');
    $params['modified_user_id'] = $userId;
  } else {
    $params['created_date'] = date('Ymd');
    $params['created_user_id'] = $userId;
  }

  return _civicrm_api3_basic_create('CRM_Civirules_BAO_Action', $params);
}
