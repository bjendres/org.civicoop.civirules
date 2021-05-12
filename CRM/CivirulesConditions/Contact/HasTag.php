<?php
use CRM_Civirules_ExtensionUtil as E;
/**
 * Class for CiviRules Contact HasTag condition
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 12 May 2021
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Contact_HasTag extends CRM_Civirules_Condition {

  protected $conditionParams = [];

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
    $entityType = $triggerData->getEntity();
    if ($entityType == 'Membership' || $entityType == 'EntityTag') {
      $entityID = $triggerData->getContactId();
  	}
    else {
      $entityID = $triggerData->getEntityId();
    }
    if (empty($entityID)) {
      return FALSE;
    }
    $generic = new CRM_CivirulesConditions_Generic_HasTag();
    $generic->setEntityTable('civicrm_contact');
    switch($this->conditionParams['operator']) {
      case 'in one of':
        $isConditionValid = $generic->entityHasOneOfTags($entityID, $this->conditionParams['tag_ids']);
        break;
      case 'in all of':
        $isConditionValid = $generic->entityHasAllTags($entityID, $this->conditionParams['tag_ids']);
        break;
      case 'not in':
        $isConditionValid = $generic->entityHasNotTag($entityID, $this->conditionParams['tag_ids']);
        break;
    }
    return $isConditionValid;
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
  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/condition/contact_hastag/', 'rule_condition_id=' . $ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   */
  public function userFriendlyConditionParams(): string {
    $generic = new CRM_CivirulesConditions_Generic_HasTag();
    $operators = $generic->getOperatorOptions();
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
    return $operatorLabel .' tags (' . $tags . ')';
  }

  /**
   * This function validates whether this condition works with the selected trigger.
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   *
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    $entities = $trigger->getProvidedEntities();
    foreach (['Contact', 'GroupContact'] as $entity) {
      if (isset($entities[$entity])) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
