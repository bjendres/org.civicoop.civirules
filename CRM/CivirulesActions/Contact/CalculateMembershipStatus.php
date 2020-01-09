<?php
/**
 * @author Björn Endres (SYSTOPIA) <endres@systopia.de>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesActions_Contact_CalculateMembershipStatus extends CRM_Civirules_Action {

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $action_params = $this->getActionParameters();
    
    // prepare API call  
    $params['id'] = $triggerData->getContactId();



    $params['job_title'] = $action_params['job_title'];

    civicrm_api3('Contact', 'create', $params);
  }

  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/contact/jobtitle', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $action_params = $this->getActionParameters();
    $label = ts("(Re)calculate membership status in '%1'", array(1=>$action_params['custom_field_label']));
    return $label;
  }
  
  /**
   * Will calculate if the given contact has an active membership
   */
  public static function calculateMembershipStatus($contact_id) {
    -17:57
  }
}