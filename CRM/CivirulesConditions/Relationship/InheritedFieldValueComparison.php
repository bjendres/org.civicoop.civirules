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
  public static $IFVC_MODE_RELATED_ONLY  = 'related_only';   // Only related contact's value
  public static $IFVC_MODE_RELATED_FIRST = 'related_first';  // Related contact's value first, main contact's if not set
  public static $IFVC_MODE_MAIN_FIRST    = 'main_first';     // Main contact's value first, related contact's if not set

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

    // TODO:
    $main_contact_data    = array();
    $related_contact_data = array();

    switch ($mode) {
      case self::$IFVC_MODE_RELATED_ONLY:
        return $this->_getFieldValue($triggerData, $field, $related_contact_data);
        break;

      case self::$IFVC_MODE_RELATED_FIRST:
        $value = $this->_getFieldValue($triggerData, $field, $related_contact_data);
        if ($value != NULL) {
          return $value;
        } else {
          return $this->_getFieldValue($triggerData, $field, $main_contact_data);
        }

      case self::$IFVC_MODE_MAIN_FIRST:
        $value = $this->_getFieldValue($triggerData, $field, $main_contact_data);
        if ($value != NULL) {
          return $value;
        } else {
          return $this->_getFieldValue($triggerData, $field, $related_contact_data);
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