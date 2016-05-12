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
  }


  protected function getModeOptions() {
    return array(
      CRM_CivirulesConditions_Relationship_InheritedFieldValueComparison::$IFVC_MODE_RELATED_ONLY  => ts("Only related contact's value"),
      CRM_CivirulesConditions_Relationship_InheritedFieldValueComparison::$IFVC_MODE_RELATED_FIRST => ts("Related contact's value first, main contact's if not set"),
      CRM_CivirulesConditions_Relationship_InheritedFieldValueComparison::$IFVC_MODE_MAIN_FIRST    => ts("Main contact's value first, related contact's if not set"),
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

    if (!empty($data['mode'])) {
      $defaultValues['mode'] = $data['mode'];
    }

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