<?php
/**
 * Class for CiviRules post trigger handling - Membership Renewed
 *
 * @license AGPL-3.0
 */

class CRM_CivirulesPostTrigger_MembershipRenewed extends CRM_Civirules_Trigger_Post {

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), 'Membership');
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Member_DAO_Membership';
  }

  /**
   * Trigger a rule for this trigger
   *
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   */
  public function triggerTrigger($op, $objectName, $objectId, $objectRef, $eventID) {
    $triggerData = $this->getTriggerDataFromPost($op, $objectName, $objectId, $objectRef, $eventID);
    $membership = $triggerData->getEntityData('Membership');
    $originalMembership = $triggerData->getOriginalData();

    // Check if the Membership has been renewed (end_date has been increased by one membership term)
    // As a membership runs from [date] to [date - 1 day] we need to check if the new end_date matches the
    //   calculated end_date based on the original end_date + 1 day.
    $startDate = date('Y-m-d', strtotime("{$originalMembership['end_date']} + 1 day"));
    $membershipDates = CRM_Member_BAO_MembershipType::getDatesForMembershipType(
      $membership['membership_type_id'], $membership['membership_join_date'], $startDate);
    if ($membershipDates['end_date'] !== CRM_Utils_Date::isoToMysql($membership['end_date'])) {
      return;
    }

    CRM_Civirules_Engine::triggerRule($this, clone $triggerData);
  }

}
