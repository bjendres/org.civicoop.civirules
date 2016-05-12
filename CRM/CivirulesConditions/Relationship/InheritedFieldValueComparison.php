<?php

/**
 * This is similar to the FieldValueComparison condition, 
 * except that it will include data from a related contact
 * to potentially override that value
 *
 * @author endres@systopia.de
 */
class CRM_CivirulesConditions_Relationship_InheritedFieldValueComparison extends CRM_CivirulesConditions_FieldValueComparison {

  /** define different inheritance modes: */
  public static $IFVC_MODE_A_ONLY  = 'a_only';   // Only related contact A's value
  public static $IFVC_MODE_B_ONLY  = 'b_only';   // Only related contact B's value
  public static $IFVC_MODE_A_FIRST = 'a_first';  // Contact A's value first, the other contact's if not set
  public static $IFVC_MODE_B_FIRST = 'b_first';  // Contact B's value first, the other contact's if not set

  /**
   * override to make sure there is relationship data
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $relationship = $triggerData->getEntityData('relationship');
    if (empty($relationship)) {
      return FALSE;
    } else {
      return parent::isConditionValid($triggerData);
    }
  }

  /**
   * Returns the value of the field for the condition
   * For example: I want to check if age > 50, this function would return the 50
   *
   * @param object CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return
   * @access protected
   * @abstract
   */
  protected function getFieldValue(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $field = $this->conditionParams['field'];
    $mode  = $this->conditionParams['mode'];

    // load contact data
    $relationship = $triggerData->getEntityData('relationship');
    $contact_a = civicrm_api3('Contact', 'getsingle', array('id' => $relationship['contact_id_a']));
    $contact_b = civicrm_api3('Contact', 'getsingle', array('id' => $relationship['contact_id_b']));

    switch ($mode) {
      case self::$IFVC_MODE_A_ONLY:
        return $this->_getFieldValue($triggerData, $field, $contact_a);

      case self::$IFVC_MODE_B_ONLY:
        return $this->_getFieldValue($triggerData, $field, $contact_b);

      case self::$IFVC_MODE_A_FIRST:
        $value = $this->_getFieldValue($triggerData, $field, $contact_a);
        if ($value !== NULL && ($value !== '')) {
          return $value;
        } else {
          return $this->_getFieldValue($triggerData, $field, $contact_b);
        }

      case self::$IFVC_MODE_B_FIRST:
        $value = $this->_getFieldValue($triggerData, $field, $contact_b);
        if ($value !== NULL && ($value !== '')) {
          return $value;
        } else {
          return $this->_getFieldValue($triggerData, $field, $contact_a);
        }
      
      default:
        // no mode set => ignore
        // TODO: proper issue warning?
        error_log("NO mode set!!");
        return NULL;
    }
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleConditionId
   * @return bool|string
   * @access public
   */
  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/condition/relationship/inheritedfieldvaluecomparison/', 'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $text = parent::userFriendlyConditionParams();
    return $text . htmlentities(" (" . ts("inherited") . ")");
  }
}