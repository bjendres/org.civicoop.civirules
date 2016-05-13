<?php
/**
 * Class for CiviRules Contact value propagation via relationship
 *
 * @author Björn Endres (SYSTPIA) <endres@systopia.de>
 * @license AGPL-3.0
 */
class CRM_CivirulesActions_Relationship_Form_PropagateValue extends CRM_CivirulesActions_Form_Form {

  /**
   * Overridden parent method to build the form
   *
   * @access public
   */
  public function buildQuickForm() {
    
    $fields = CRM_Civirules_Utils::getFields('Contact', 'CRM_Contact_DAO_Contact');

    $this->add('hidden', 'rule_action_id');
    $this->add('select', 'mode', ts('Mode'), $this->getModeOptions(), true);
    $this->add('select', 'field', ts('Field'), $fields, true, array('class' => 'crm-select2'));

    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }


  protected function getModeOptions() {
    return array(
      CRM_CivirulesActions_Relationship_PropagateValue::$IVA_MODE_A_TO_B      => ts("Override contact B's value with contact A's"),
      CRM_CivirulesActions_Relationship_PropagateValue::$IVA_MODE_B_TO_A      => ts("Override contact A's value with contact B's"),
      CRM_CivirulesActions_Relationship_PropagateValue::$IVA_MODE_A_TO_B_FILL => ts("Fill contact B's value with contact A's"),
      CRM_CivirulesActions_Relationship_PropagateValue::$IVA_MODE_B_TO_A_FILL => ts("Fill contact A's value with contact B's"),
      );
  }


  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $defaultValues['rule_action_id'] = $this->ruleActionId;
    if (!empty($this->ruleAction->action_params)) {
      $data = unserialize($this->ruleAction->action_params);
    }

    if (isset($data['mode'])) {
      $defaultValues['mode'] = $data['mode'];
    }

    if (isset($data['field'])) {
      $defaultValues['field'] = $data['field'];
    }

    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data['mode']  = $this->_submitValues['mode'];
    $data['field'] = $this->_submitValues['field'];
    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }
}