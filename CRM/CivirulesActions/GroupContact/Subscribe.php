<?php

/**
 * Class for CiviRules Group Contact add action.
 *
 * Adds a user to a group
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesActions_GroupContact_Subscribe extends CRM_Civirules_Action {

  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $actionParams = $this->getActionParameters();
    $groupIds = array();
    if (!empty($actionParams['group_id'])) {
      $groupIds = array($actionParams['group_id']);
    } elseif (!empty($actionParams['group_ids']) && is_array($actionParams['group_ids'])) {
      $groupIds = $actionParams['group_ids'];
    }

    foreach($groupIds as $groupId) {
      try {
        $email = civicrm_api3('Email', 'getvalue', ['contact_id' => $triggerData->getContactId(), 'is_primary' => 1, 'return' => 'email']);
        civicrm_api3('MailingEventSubscribe', 'create', [
          'contact_id' => $triggerData->getContactId(),
          'email' => $email,
          'group_id' => $groupId
        ]);
      } catch (\CiviCRM_API3_Exception $ex) {
        // Do nothing.
      }
    }
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   * @return bool|string
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/groupcontact', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $params = $this->getActionParameters();
    if (!empty($params['group_id'])) {
      try {
        $group = civicrm_api3('Group', 'getvalue', [
          'return' => 'title',
          'id' => $params['group_id']
        ]);
        return ts('Subscribe contact to group(s): %1', array(1 => $group));
      } catch (Exception $e) {
        return '';
      }
    } elseif (!empty($params['group_ids']) && is_array($params['group_ids'])) {
      $groups = '';
      foreach($params['group_ids'] as $group_id) {
        try {
          $group = civicrm_api3('Group', 'getvalue', [
            'return' => 'title',
            'id' => $group_id
          ]);
          if (strlen($groups)) {
            $groups .= ', ';
          }
          $groups .= $group;
        } catch (Exception $e) {
          // Do nothing.
        }
      }
      return ts('Subscribe contact to group(s): %1', array(1 => $groups));
    }
    return '';
  }


}
