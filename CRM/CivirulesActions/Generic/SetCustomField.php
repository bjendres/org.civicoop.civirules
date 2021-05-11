<?php
/**
 * Class for CiviRules Set Custom Field
 *
 * @author Björn Endres (SYSTOPIA) <endres@systopia.de>
 * @license AGPL-3.0
 */

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesActions_Generic_SetCustomField extends CRM_Civirules_Action {

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $action_params = $this->getActionParameters();
    $field_id = $action_params['field_id'];

    // Get the entity the custom field extends.
    $entity = civicrm_api3('CustomField', 'getsingle', [
      'return' => ['custom_group_id.extends'],
      'id' => $field_id,
    ])['custom_group_id.extends'];
    if (in_array($entity, ['Individual', 'Organization', 'Household'])) {
      $entity = 'Contact';
    }

    // Get the ID of the entity we're updating.
    $entityId = $triggerData->getEntityId();


    // get the value from the configuration
    $new_value = $action_params['value'];
    // check if it's json
    $json_value = json_decode($new_value, 1);
    if ($json_value !== null) {
      $new_value = $json_value;
    }

    // Ensure the new value isn't the same, to prevent unnecessary writes and avoid infinite loops.
    $existingRecord = civicrm_api3($entity, 'get', [
      'id'                 => $entityId,
      "custom_{$field_id}" => $new_value,
    ]);
    if (!$existingRecord['count']) {
      // set the new value using the API
      civicrm_api3($entity, 'create', [
        'id'                 => $entityId,
        "custom_{$field_id}" => $new_value,
      ]);
    }

  }

  /**
   * Method to return the url for additional form processing for action
   * and return false if none is needed
   *
   * @param int $ruleActionId
   * @return bool
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/generic/setcustomvalue', 'rule_action_id='.$ruleActionId);
  }

  /**
   * This function validates whether this action works with the selected trigger.
   *
   * This function could be overriden in child classes to provide additional validation
   * whether an action is possible in the current setup.
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    return TRUE;
  }
}
