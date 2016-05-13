<?php
/**
 * Class for CiviRules Contact value propagation via relationship
 *
 * @author Björn Endres (SYSTPIA) <endres@systopia.de>
 * @license AGPL-3.0
 */
class CRM_CivirulesActions_Relationship_PropagateValue extends CRM_Civirules_Action {

  public static $IVA_MODE_A_TO_B      = 'a-b_override';
  public static $IVA_MODE_B_TO_A      = 'b-a_override';
  public static $IVA_MODE_A_TO_B_FILL = 'a-b_fill';
  public static $IVA_MODE_B_TO_A_FILL = 'b-a_fill';

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    error_log("RUN!");
    $contribution = $triggerData->getEntityData('Contribution');
    $actionParams = $this->getActionParameters();

    // load contact data
    $relationship = $triggerData->getEntityData('relationship');
    error_log($relationship);
    if (empty($relationship)) return;

    $field = $actionParams['field'];
    error_log($field);

    // this doesn't work with contacts in trash:
    // $contact_ids[] = $relationship['contact_id_a'];
    // $contact_ids[] = $relationship['contact_id_b'];
    // $contacts = civicrm_api3('Contact', 'get', array('id' => array('IN', $contact_ids)));
    // $contact_a = $contacts['values'][$relationship['contact_id_a']];
    // $contact_b = $contacts['values'][$relationship['contact_id_b']];

    $contact_a = civicrm_api3('Contact', 'getsingle', array('id' => $relationship['contact_id_a']));
    $contact_b = civicrm_api3('Contact', 'getsingle', array('id' => $relationship['contact_id_b']));

    switch ($actionParams['mode']) {
      case self::$IVA_MODE_A_TO_B:
        if (empty($contact_a)) return;
        $params = array(
          'id'   => $relationship['contact_id_b'], 
          $field => CRM_Utils_Array::value($field, $contact_a));
        break;

      case self::$IVA_MODE_B_TO_A:
        if (empty($contact_b)) return;
        $params = array(
          'id'   => $relationship['contact_id_a'],
          $field => CRM_Utils_Array::value($field, $contact_b));
        break;
      
      case self::$IVA_MODE_A_TO_B_FILL:
        if (empty($contact_a)) return;
        if (empty($contact_b)) return;
        $current_value = CRM_Utils_Array::value($field, $contact_b);
        if ($current_value == NULL && $current_value == '') {
          $params = array(
            'id'   => $relationship['contact_id_b'], 
            $field => CRM_Utils_Array::value($field, $contact_a));
        }
        break;

      case self::$IVA_MODE_B_TO_A_FILL:
        if (empty($contact_a)) return;
        if (empty($contact_b)) return;
        $current_value = CRM_Utils_Array::value($field, $contact_a);
        if ($current_value == NULL && $current_value == '') {
          $params = array(
            'id'   => $relationship['contact_id_a'], 
            $field => CRM_Utils_Array::value($field, $contact_b));
        }
        break;

      default:
        // TODO: log error
        break;
    }
    
    if (!empty($params)) {
      try {
        civicrm_api3('Contact', 'Create', $params);
      } catch (CiviCRM_API3_Exception $ex) {}      
    }
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   * @return bool|string
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/relationship/propagatevalue', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $return = "";
    $dateString = "";
    $params = $this->getActionParameters();
    if (isset($params['thank_you_radio'])) {
      switch ($params['thank_you_radio']) {
        case 0:
          $dateString = "date action executes";
          break;
        case 1:
          $dateString = $params['number_of_days']." days after action executes";
          break;
        case 2:
          $dateString = date('d M Y', strtotime($params['thank_you_date']));
          break;
      }
    }
    if (!empty($dateString)) {
      $return = 'Thank You Date for Contribution will be set to : ' . $dateString;
    }
    return $return;
  }
}