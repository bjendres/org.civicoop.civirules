<?php
/**
 * Class for CiviRules generic HasTag condition
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Generic_HasTag extends CRM_Civirules_Condition {

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
    $entityID = $triggerData->getEntityId();

    if (empty($entityID)) {
      return FALSE;
    }
    switch($this->conditionParams['operator']) {
      case 'in one of':
        $isConditionValid = $this->entityHasOneOfTags($entityID, $this->conditionParams['tag_ids'], $triggerData->getEntity());
        break;
      case 'in all of':
        $isConditionValid = $this->entityHasAllTags($entityID, $this->conditionParams['tag_ids'], $triggerData->getEntity());
        break;
      case 'not in':
        $isConditionValid = $this->entityHasNotTag($entityID, $this->conditionParams['tag_ids'], $triggerData->getEntity());
        break;
    }
    return $isConditionValid;
  }

  /**
   * @param int $entityID
   * @param array $tag_ids
   * @param string $entity
   *
   * @return bool
   */
  protected function entityHasNotTag(int $entityID, array $tag_ids, string $entity = 'Contact'): bool {
    $isValid = TRUE;

    $tags = \Civi\Api4\EntityTag::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('tag_id')
      ->addWhere('entity_table:name', '=', $entity)
      ->addWhere('entity_id', '=', $entityID)
      ->execute()->column('tag_id');
    foreach($tag_ids as $tag_id) {
      if (in_array($tag_id, $tags)) {
        $isValid = FALSE;
      }
    }

    return $isValid;
  }

  /**
   * @param int $entityID
   * @param array $tag_ids
   * @param string $entity
   *
   * @return bool
   */
  protected function entityHasAllTags(int $entityID, array $tag_ids, string $entity = 'Contact'): bool {
    $isValid = 0;

    $tags = \Civi\Api4\EntityTag::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('tag_id')
      ->addWhere('entity_table:name', '=', $entity)
      ->addWhere('entity_id', '=', $entityID)
      ->execute()->column('tag_id');
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
   * @param int $entityID
   * @param array $tag_ids
   * @param string $entity
   *
   * @return bool
   */
  protected function entityHasOneOfTags(int $entityID, array $tag_ids, string $entity = 'Contact'): bool {
    $isValid = FALSE;

    $tags = \Civi\Api4\EntityTag::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('tag_id')
      ->addWhere('entity_table:name', '=', $entity)
      ->addWhere('entity_id', '=', $entityID)
      ->execute()->column('tag_id');
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/contact_hastag/', 'rule_condition_id=' . $ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   */
  public function userFriendlyConditionParams(): string {
    $operators = self::getOperatorOptions();
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
    foreach (['Contact', 'Activity', 'Case', 'File'] as $entity) {
      if (isset($entities[$entity])) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
