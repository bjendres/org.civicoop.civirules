<?php

require_once 'civirules.civix.php';
if (!interface_exists("\\Psr\\Log\\LoggerInterface")) {
  require_once('psr/log/LoggerInterface.php');
}
if (!class_exists("\\Psr\\Log\\LogLevel")) {
  require_once('psr/log/LogLevel.php');
}

use CRM_Civirules_ExtensionUtil as E;

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civirules_civicrm_config(&$config) {
  if (isset(Civi::$statics[__FUNCTION__])) { return; }
  Civi::$statics[__FUNCTION__] = 1;
  _civirules_civix_civicrm_config($config);

  // eventID param added in 5.34: https://github.com/civicrm/civicrm-core/pull/19209
  if (version_compare(CRM_Utils_System::version(), '5.34', '>=')) {
    // These events were added in 5.26: https://github.com/civicrm/civicrm-core/pull/16714
    \Civi::dispatcher()->addListener('civi.dao.preUpdate', 'civirules_trigger_preupdate');
    \Civi::dispatcher()->addListener('civi.dao.postUpdate', 'civirules_trigger_postupdate');
    \Civi::dispatcher()->addListener('civi.dao.preInsert', 'civirules_trigger_preinsert');
    \Civi::dispatcher()->addListener('civi.dao.postInsert', 'civirules_trigger_postinsert');
    \Civi::dispatcher()->addListener('civi.dao.preDelete', 'civirules_trigger_predelete');
    \Civi::dispatcher()->addListener('civi.dao.postDelete', 'civirules_trigger_postdelete');
  }

  // We add the "old" hooks because sometimes the DAO is bypassed (eg. GroupContact)
  \Civi::dispatcher()->addListener('hook_civicrm_pre', 'civirules_symfony_civicrm_pre');
  \Civi::dispatcher()->addListener('hook_civicrm_post', 'civirules_symfony_civicrm_post');
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function civirules_civicrm_xmlMenu(&$files) {
  _civirules_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civirules_civicrm_install() {
  _civirules_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function civirules_civicrm_postInstall() {
  _civirules_civix_civicrm_postInstall();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function civirules_civicrm_uninstall() {
  _civirules_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civirules_civicrm_enable() {
  _civirules_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function civirules_civicrm_disable() {
  _civirules_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function civirules_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civirules_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function civirules_civicrm_managed(&$entities) {
  // First create a backup because the managed entities are gone
  // so the actions and conditions table are first going to be emptied
  _civirules_upgrade_to_2x_backup();
  // Check triggers, actions and conditions
  CRM_Civirules_Utils_Upgrader::insertTriggersFromJson(E::path('sql/triggers.json'));
  CRM_Civirules_Utils_Upgrader::insertActionsFromJson(E::path('sql/actions.json'));
  CRM_Civirules_Utils_Upgrader::insertConditionsFromJson(E::path('sql/conditions.json'));
  _civirules_civix_civicrm_managed($entities);
}

/**
 * Helper function to create a backup if the current schema version is of a 1.x version.
 * We need this backup to restore missing actions and rules after upgrading.
 */
function _civirules_upgrade_to_2x_backup() {
  // Check schema version
  // Schema version 1023 is inserted by a 2x version
  // So if the schema version is lower than 1023 we are still on a 1x version.
  $schemaVersion = CRM_Core_DAO::singleValueQuery("SELECT schema_version FROM civicrm_extension WHERE `name` = 'CiviRules'");
  if ($schemaVersion >= 1023) {
    return; // No need for preparing the update.
  }

  if (!CRM_Core_DAO::checkTableExists('civirule_rule_action_backup')) {
    // Backup the current action and condition connected to a civirule
    CRM_Core_DAO::executeQuery("
      CREATE TABLE `civirule_rule_action_backup`
      SELECT `civirule_rule_action`.*, `civirule_action`.`class_name` as `action_class_name`
      FROM `civirule_rule_action`
      INNER JOIN `civirule_action` ON `civirule_rule_action`.`action_id` = `civirule_action`.`id`
    ");
  }
  if (!CRM_Core_DAO::checkTableExists('civirule_rule_action_backup')) {
    CRM_Core_DAO::executeQuery("
      CREATE TABLE `civirule_rule_condition_backup`
      SELECT `civirule_rule_condition`.*, `civirule_condition`.`class_name` as `condition_class_name`
      FROM `civirule_rule_condition`
      INNER JOIN `civirule_condition` ON `civirule_rule_condition`.`condition_id` = `civirule_condition`.`id`
    ");
  }
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civirules_civicrm_caseTypes(&$caseTypes) {
  _civirules_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function civirules_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civirules_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook civicrm_navigationMenu
 * to create a CiviRules menu item in the Administer menu
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function civirules_civicrm_navigationMenu(&$menu) {
  _civirules_civix_insert_navigation_menu($menu, 'Administer', [
    'label' => E::ts('CiviRules'),
    'name' => 'CiviRules',
    'url' => NULL,
    'permission' => 'administer CiviCRM',
    'operator' => NULL,
    'separator' => NULL,
  ]);

  _civirules_civix_insert_navigation_menu($menu, 'Administer/CiviRules', [
    'label' => E::ts('Manage Rules'),
    'name' => 'Manage Rules',
    'url' => CRM_Utils_System::url('civicrm/civirules/form/rulesview', 'reset=1', TRUE),
    'permission' => 'administer CiviCRM',
    'operator' => NULL,
    'separator' => 0,
  ]);

  _civirules_civix_insert_navigation_menu($menu, 'Administer/CiviRules', [
    'label' => E::ts('New Rule'),
    'name' => 'New Rule',
    'url' => CRM_Utils_System::url('civicrm/civirule/form/rule', 'reset=1&action=add', TRUE),
    'permission' => 'administer CiviCRM',
    'operator' => NULL,
    'separator' => 0,
  ]);

  $optionGroup = CRM_Civirules_Utils_OptionGroup::getSingleWithName('civirule_rule_tag');
  if (isset($optionGroup['id']) && !empty($optionGroup['id'])) {
    $ruleTagUrl = CRM_Utils_System::url('civicrm/admin/options', 'reset=1&gid=' . $optionGroup['id'], TRUE);
    _civirules_civix_insert_navigation_menu($menu, 'Administer/CiviRules', [
      'label' => E::ts('CiviRule Tags'),
      'name' => E::ts('CiviRules Tags'),
      'url' => $ruleTagUrl,
      'permission' => 'administer CiviCRM',
      'operator' => NULL,
      'separator' => 0,
    ]);
  }
  _civirules_civix_navigationMenu($menu);
}


function civirules_symfony_civicrm_pre($event) {
  // New style pre/post Delete/Insert/Update events exist from 5.34.
  if (version_compare(CRM_Utils_System::version(), '5.34', '>=')) {
    // GroupContact "add" does not use the DAO:
    // https://lab.civicrm.org/extensions/civirules/-/issues/123
    // because CRM_Contact_BAO_GroupContact::bulkAddContactsToGroup circumvents dao->save() by directly writing to the DB in a query
    // So we always process it via the "old" hooks.
    $entities = ['GroupContact'];
    if (!in_array($event->entity, $entities)) {
      return;
    }
  }

  CRM_Civirules_Utils_PreData::pre($event->action, $event->entity, $event->id, $event->params, 1);
  CRM_Civirules_Utils_CustomDataFromPre::pre($event->action, $event->entity, $event->id, $event->params, 1);
}

function civirules_symfony_civicrm_post($event) {
  // New style pre/post Delete/Insert/Update events exist from 5.34.
  if (version_compare(CRM_Utils_System::version(), '5.34', '>=')) {
    // GroupContact "add" does not use the DAO:
    // https://lab.civicrm.org/extensions/civirules/-/issues/123
    // because CRM_Contact_BAO_GroupContact::bulkAddContactsToGroup circumvents dao->save() by directly writing to the DB in a query
    // So we always process it via the "old" hooks.
    $entities = ['GroupContact'];
    if (!in_array($event->entity, $entities)) {
      return;
    }
  }

  if (CRM_Core_Transaction::isActive()) {
    CRM_Core_Transaction::addCallback(CRM_Core_Transaction::PHASE_POST_COMMIT, 'civirules_civicrm_post_callback', [$event->action, $event->entity, $event->id, $event->object, 1]);
  }
  else {
    civirules_civicrm_post_callback($event->action, $event->entity, $event->id, $event->object, 1);
  }
}

function civirules_trigger_preinsert($event) {
  CRM_Civirules_Utils_PreData::pre('create', CRM_Core_DAO_AllCoreTables::getBriefName(get_class($event->object)), $event->object->id, $event->object, $event->eventID);
  CRM_Civirules_Utils_CustomDataFromPre::pre('create', CRM_Core_DAO_AllCoreTables::getBriefName(get_class($event->object)), $event->object->id, $event->object, $event->eventID);
}

function civirules_trigger_postinsert($event) {
  if (CRM_Core_Transaction::isActive()) {
    CRM_Core_Transaction::addCallback(CRM_Core_Transaction::PHASE_POST_COMMIT, 'civirules_civicrm_post_callback', ['create', CRM_Core_DAO_AllCoreTables::getBriefName(get_class($event->object)), $event->object->id, $event->object, $event->eventID]);
  }
  else {
    civirules_civicrm_post_callback('create', CRM_Core_DAO_AllCoreTables::getBriefName(get_class($event->object)), $event->object->id, $event->object, $event->eventID);
  }
}

function civirules_trigger_preupdate($event) {
  CRM_Civirules_Utils_PreData::pre('edit', CRM_Core_DAO_AllCoreTables::getBriefName(get_class($event->object)), $event->object->id, $event->object, $event->eventID);
  CRM_Civirules_Utils_CustomDataFromPre::pre('edit', CRM_Core_DAO_AllCoreTables::getBriefName(get_class($event->object)), $event->object->id, $event->object, $event->eventID);
}

function civirules_trigger_postupdate($event) {
  if (CRM_Core_Transaction::isActive()) {
    CRM_Core_Transaction::addCallback(CRM_Core_Transaction::PHASE_POST_COMMIT, 'civirules_civicrm_post_callback', ['edit', CRM_Core_DAO_AllCoreTables::getBriefName(get_class($event->object)), $event->object->id, $event->object, $event->eventID]);
  }
  else {
    civirules_civicrm_post_callback('edit', CRM_Core_DAO_AllCoreTables::getBriefName(get_class($event->object)), $event->object->id, $event->object, $event->eventID);
  }
}

function civirules_trigger_predelete($event) {
  CRM_Civirules_Utils_PreData::pre('delete', CRM_Core_DAO_AllCoreTables::getBriefName(get_class($event->object)),
    $event->object->id, $event->object, $event->eventID ?? 1);
  CRM_Civirules_Utils_CustomDataFromPre::pre('delete', CRM_Core_DAO_AllCoreTables::getBriefName(get_class
  ($event->object)), $event->object->id, $event->object, $event->eventID ?? 1);
}

function civirules_trigger_postdelete($event) {
  if (CRM_Core_Transaction::isActive()) {
    CRM_Core_Transaction::addCallback(CRM_Core_Transaction::PHASE_POST_COMMIT, 'civirules_civicrm_post_callback', ['delete', CRM_Core_DAO_AllCoreTables::getBriefName(get_class($event->object)), $event->object->id, $event->object, $event->eventID ?? 1]);
  }
  else {
    civirules_civicrm_post_callback('delete', CRM_Core_DAO_AllCoreTables::getBriefName(get_class($event->object)),
      $event->object->id, $event->object, $event->eventID ?? 1);
  }
}

function civirules_civicrm_post_callback($op, $objectName, $objectId, $objectRef, $eventID) {
  CRM_Civirules_Trigger_Post::post($op, $objectName, $objectId, $objectRef, $eventID);
}

function civirules_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  CRM_CivirulesPostTrigger_CaseCustomDataChanged::validateForm($form);
  CRM_CivirulesPostTrigger_ContactCustomDataChanged::validateForm($form);
  CRM_CivirulesPostTrigger_IndividualCustomDataChanged::validateForm($form);
  CRM_CivirulesPostTrigger_OrganizationCustomDataChanged::validateForm($form);
  CRM_CivirulesPostTrigger_HouseholdCustomDataChanged::validateForm($form);
}

function civirules_civicrm_custom($op, $groupID, $entityID, &$params) {
  /**
   * Fix/Hack for issue #208 (https://github.com/CiviCooP/org.civicoop.civirules/issues/208)
   *
   * To reproduce:
   * - create a custom data set for contacts that supports multiple records
   * - create a rule that triggers on custom data changing
   * - add a record to that custom data set for a contact
   * - delete the record
   * - observe the logs
   *
   * This returns the error: "Expected one Contact but found 25"
   * Traced to CRM/CivirulesPostTrigger/ContactCustomDataChanged.php where there is an api call to contacts getsingle. The issue is that when the custom data record is deleted, there is no remaining entity_id with which to retrieve the contact, and so no id is passed to the getsingle call.
   *
   * The fix is to check whether the $op is delete and whether $entityID is empty and then check
   * whether the contactID is provided in the url.
   */
  if ($op == 'delete' && empty($entityID)) {
    $contactId = CRM_Utils_Request::retrieve('contactId', 'Positive');
    if (!empty($contactId)) {
      $entityID = $contactId;
    }
  }
  /** End ugly hack */

  CRM_CivirulesPostTrigger_CaseCustomDataChanged::custom($op, $groupID, $entityID, $params);
  CRM_CivirulesPostTrigger_ContactCustomDataChanged::custom($op, $groupID, $entityID, $params);
  CRM_CivirulesPostTrigger_IndividualCustomDataChanged::custom($op, $groupID, $entityID, $params);
  CRM_CivirulesPostTrigger_OrganizationCustomDataChanged::custom($op, $groupID, $entityID, $params);
  CRM_CivirulesPostTrigger_HouseholdCustomDataChanged::custom($op, $groupID, $entityID, $params);
}

function civirules_civirules_alter_trigger_data(CRM_Civirules_TriggerData_TriggerData &$triggerData) {
  //also add the custom data which is passed to the pre hook (and not the post)
  CRM_Civirules_Utils_CustomDataFromPre::addCustomDataToTriggerData($triggerData);
}

/**
 * Register extensions entities.
 *
 * Required for api calls.
 *
 * @param array $entityTypes
 */
function civirules_civicrm_entityTypes(&$entityTypes) {
  _civirules_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_apiWrappers()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_apiWrappers/
 */
function civirules_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  if ($apiRequest['entity'] == 'Contact' && $apiRequest['action'] == 'create') {
    $wrappers[] = new CRM_Civirules_TrashRestoreApiWrapper();
  }
}
