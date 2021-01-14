<?php
/**
 * Class for CiviRules AgeComparison (extending generic ValueComparison)
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Contact_HasTag extends CRM_Civirules_Condition {

  private $conditionParams = [];

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   */
  public function setRuleConditionData(array $ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    $this->conditionParams = [];
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  /**
   * This method returns TRUE or FALSE when an condition is valid or not
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   *
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData): bool {
    $isConditionValid = FALSE;
    $contact_id = $triggerData->getContactId();
    if (empty($contact_id)) {
      return FALSE;
    }
    switch($this->conditionParams['operator']) {
      case 'in one of':
        $isConditionValid = $this->contactHasOneOfTags($contact_id, $this->conditionParams['tag_ids']);
        break;
      case 'in all of':
        $isConditionValid = $this->contactHasAllTags($contact_id, $this->conditionParams['tag_ids']);
        break;
      case 'not in':
        $isConditionValid = $this->contactHasNotTag($contact_id, $this->conditionParams['tag_ids']);
        break;
    }
    return $isConditionValid;
  }

  /**
   * @param int $contact_id
   * @param array $tag_ids
   *
   * @return bool
   */
  protected function contactHasNotTag(int $contact_id, array $tag_ids): bool {
    $isValid = TRUE;

    $tags = CRM_Core_BAO_EntityTag::getTag($contact_id);
    foreach($tag_ids as $tag_id) {
      if (in_array($tag_id, $tags)) {
        $isValid = FALSE;
      }
    }

    return $isValid;
  }

  /**
   * @param int $contact_id
   * @param array $tag_ids
   *
   * @return bool
   */
  protected function contactHasAllTags(int $contact_id, array $tag_ids): bool {
    $isValid = 0;

    $tags = CRM_Core_BAO_EntityTag::getTag($contact_id);
    foreach($tag_ids as $tag_id) {
      if (in_array($tag_id, $tags)) {
        $isValid++;
      }
    }

    if (count($tag_ids) == $isValid && count($tag_ids) > 0) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @param int $contact_id
   * @param array $tag_ids
   *
   * @return bool
   */
  protected function contactHasOneOfTags(int $contact_id, array $tag_ids): bool {
    $isValid = FALSE;

    $tags = CRM_Core_BAO_EntityTag::getTag($contact_id);
    foreach($tag_ids as $tag_id) {
      if (in_array($tag_id, $tags)) {
        $isValid = TRUE;
        break;
      }
    }

    return $isValid;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return FALSE if you do not need extra data input
   *
   * @param int $ruleConditionId
   *
   * @return bool|string
   */
  public function getExtraDataInputUrl(int $ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/condition/contact_hastag/', 'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   */
  public function userFriendlyConditionParams(): string {
    $operators = CRM_CivirulesConditions_Contact_InGroup::getOperatorOptions();
    $operator = $this->conditionParams['operator'];
    $operatorLabel = ts('unknown');
    if (isset($operators[$operator])) {
      $operatorLabel = $operators[$operator];
    }

    $tags = '';
    foreach($this->conditionParams['tag_ids'] as $tid) {
      if (strlen($tags)) {
        $tags .= ', ';
      }
      $tags .= civicrm_api3('Tag', 'getvalue', ['return' => 'name', 'id' => $tid]);
    }

    return $operatorLabel.' tags ('.$tags.')';
  }

  /**
   * Method to get operators
   *
   * @return array
   */
  public static function getOperatorOptions(): array {
    return [
      'in one of' => ts('In one of selected'),
      'in all of' => ts('In all selected'),
      'not in' => ts('Not in selected'),
    ];
  }

}
