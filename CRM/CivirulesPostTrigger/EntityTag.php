<?php

class CRM_CivirulesPostTrigger_EntityTag extends CRM_Civirules_Trigger_Post {

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), 'EntityTag');
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Core_DAO_EntityTag';
  }

  /**
   * Trigger a rule for this trigger
   *
   * @param string $op
   * @param string $objectName
   * @param int $objectId
   * @param object $objectRef
   * @param string $eventID
   */
  public function triggerTrigger($op, $objectName, $objectId, $objectRef, $eventID) {

    $entity = CRM_Civirules_Utils_ObjectName::convertToEntity($objectName);

    //only execute entity tag for setting or removing tags from contacts
    //because we need to know the contact id for the trigger engine
    //and we only know this when the tag is on contact level
    if (!isset($objectRef->entity_table) || $objectRef->entity_table != 'civicrm_contact') {
      return;
    }

    $data = [
      'tag_id' => $objectId,
      'entity_id' => $objectRef->entity_id,
      'entity_table' => $objectRef->entity_table,
      'contact_id' => $objectRef->entity_id,
    ];
    $triggerData = new CRM_Civirules_TriggerData_Post($entity, $objectId, $data);
    CRM_Civirules_Engine::triggerRule($this, $triggerData);
  }

}
