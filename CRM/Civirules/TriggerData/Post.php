<?php

class CRM_Civirules_TriggerData_Post extends CRM_Civirules_TriggerData_TriggerData {

  public function __construct($entity, $objectId, $data) {
    parent::__construct();
    $this->setEntity($entity);
    $this->setEntityId($objectId);
    if ($entity === 'Contact') {
      $this->setContactId($objectId);
    }
    elseif (isset($data['contact_id'])) {
      $this->setContactId($data['contact_id']);
    }
    else {
      switch ($entity) {
        case 'Membership':
          $this->setContactId(
            civicrm_api3('Membership', 'getvalue', [
            'return' => 'contact_id',
            'id' => $objectId,
            ])
          );
          $data['contact_id'] = $this->getContactId();
          break;
      }
    }
    $this->setEntityData($entity, $data);
  }

}
