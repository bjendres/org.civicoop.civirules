<?php
/**
 * Class for CiviRules Group Contact Action Form
 *
 * @author Björn Endres (SYSTOPIA) <endres@systopia.de>
 * @license AGPL-3.0
 */

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesActions_Contact_CopyCustomField extends CRM_Civirules_Action {

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $contactId = $triggerData->getContactId();
    $action_params = $this->getActionParameters();

    $copy_from_field_id = $action_params['copy_from_field_id'];
    $new_value = "";
    try {
      $new_value = civicrm_api3('Contact', 'getvalue', ['id' => $contactId, 'return' => 'custom_' . $copy_from_field_id]);
    } catch (\CiviCRM_API3_Exception $ex) {
      // Do nothing.
    }

    // set the new value using the API
    $field_id = $action_params['field_id'];
    civicrm_api3('Contact', 'create', [
      'id'                 => $contactId,
      "custom_{$field_id}" => $new_value]
    );
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
    return CRM_Utils_System::url('civicrm/civirule/form/action/contact/copycustomvalue', 'rule_action_id='.$ruleActionId);
  }
}
