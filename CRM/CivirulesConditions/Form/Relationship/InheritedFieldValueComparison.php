<?php

class CRM_CivirulesConditions_Form_Relationship_InheritedFieldValueComparison extends CRM_CivirulesConditions_Form_FieldValueComparison {

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    parent::buildQuickForm();

    $this->add('hidden', 'rule_condition_id');
    $this->add('select', 'mode', ts('Mode'), $this->getModeOptions(), true);
    $this->add('select', 'field', ts('Field'), $this->getFields(), true);
    $this->assign('custom_field_multi_select_html_types', CRM_Civirules_Utils_CustomField::getMultiselectTypes());

    // replace 'entity' field
    $this->removeElement('entity');
    $this->add('hidden', 'entity', 'Contact');

  }


  protected function getModeOptions() {
    return array(
      CRM_CivirulesConditions_Relationship_InheritedFieldValueComparison::$IFVC_MODE_A_ONLY  => ts("Only related contact A's value"),
      CRM_CivirulesConditions_Relationship_InheritedFieldValueComparison::$IFVC_MODE_B_ONLY  => ts("Only related contact B's value"),
      CRM_CivirulesConditions_Relationship_InheritedFieldValueComparison::$IFVC_MODE_A_FIRST => ts("Contact A's value first, the other contact's if not set"),
      CRM_CivirulesConditions_Relationship_InheritedFieldValueComparison::$IFVC_MODE_B_FIRST => ts("Contact B's value first, the other contact's if not set"),
      );
  }

  protected function getEntities() {
    // we only work on contact values
    return array('Contact');
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();

    $data = array();
    $ruleCondition = new CRM_Civirules_BAO_RuleCondition();
    $ruleCondition->id = $this->ruleConditionId;
    if ($ruleCondition->find(true)) {
      $data = unserialize($ruleCondition->condition_params);
    }

    if (!empty($data['mode'])) {
      $defaultValues['mode'] = $data['mode'];
    }

    $defaultValues['entity'] = 'Contact';

    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   * @access public
   */
  public function postProcess() {
    
    $data['operator'] = $this->_submitValues['operator'];
    $data['value'] = $this->_submitValues['value'];
    $data['multi_value'] = explode("\r\n", $this->_submitValues['multi_value']);
    $data['mode'] = $this->_submitValues['mode'];
    $data['entity'] = 'Contact';
    $data['field'] = substr($this->_submitValues['field'], strlen($data['entity'].'_'));

    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();

    $session = CRM_Core_Session::singleton();
    $session->setStatus('Condition '.$this->condition->label .'Parameters updated to CiviRule '
      .$this->rule->label,
      'Condition parameters updated', 'success');

    $redirectUrl = CRM_Utils_System::url('civicrm/civirule/form/rule', 'action=update&id='.$this->rule->id, TRUE);
    CRM_Utils_System::redirect($redirectUrl);
  }
}