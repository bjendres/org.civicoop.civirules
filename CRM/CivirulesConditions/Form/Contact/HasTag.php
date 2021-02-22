<?php
/**
 * Class for CiviRules Condition parameters form - entity has tag
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Form_Contact_HasTag extends CRM_CivirulesConditions_Form_Form {

  /**
   * Method to get tags
   *
   * @return array
   */
  protected function getTags() {
    $bao = new CRM_Core_BAO_Tag();
    switch ($this->trigger->object_name) {
      case 'Contact':
      case 'Membership':
        $tableName = 'civicrm_contact';
        break;

      case 'Activity':
        $tableName = 'civicrm_activity';
        break;

      case 'Case':
        $tableName = 'civicrm_case';
        break;

      case 'File':
        $tableName = 'civicrm_file';
        break;

      default:
        return [];
    };

    $tags = $bao->getTree($tableName);
    $options = [];
    foreach ($tags as $tag_id => $tag) {
      $parent = '';
      $this->buildOptionsFromTree($options, $tags, $parent);
    }
    asort($options);
    return $options;
  }

  /**
   * @param array $options
   * @param array $tree
   * @param string $parent
   */
  protected function buildOptionsFromTree(array &$options, array $tree, string $parent) {
    foreach ($tree as $tag_id => $tag) {
      if ($tag['is_selectable']) {
        $options[$tag_id] = trim($parent . ' ' . $tag['name']);
      }
      if (isset($tag['children']) && is_array($tag['children'])) {
        $this->buildOptionsFromTree($options, $tag['children'], $tag['name'] . ':');
      }
    }
  }

  /**
   * Method to get operators
   *
   * @return array
   */
  protected function getOperators() {
    return CRM_CivirulesConditions_Generic_HasTag::getOperatorOptions();
  }

  /**
   * Overridden parent method to build form
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $tag = $this->add('select', 'tag_ids', ts('Tags'), $this->getTags(), TRUE);
    $tag->setMultiple(TRUE);
    $this->add('select', 'operator', ts('Operator'), $this->getOperators(), TRUE);

    $this->addButtons([
      ['type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,],
      ['type' => 'cancel', 'name' => ts('Cancel')]
    ]);
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $data = unserialize($this->ruleCondition->condition_params);
    if (!empty($data['tag_ids'])) {
      $defaultValues['tag_ids'] = $data['tag_ids'];
    }
    if (!empty($data['operator'])) {
      $defaultValues['operator'] = $data['operator'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   */
  public function postProcess() {
    $data['tag_ids'] = $this->_submitValues['tag_ids'];
    $data['operator'] = $this->_submitValues['operator'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();

    parent::postProcess();
  }

}
