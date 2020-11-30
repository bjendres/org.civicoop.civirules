<?php

/**
 * Class CRM_Civirules_Utils_HookInvoker
 *
 * This class invokes hooks through the civicrm core hook invoker functionality
 */
class CRM_Civirules_Utils_HookInvoker {

  private static $singleton;

  private function __construct() {

  }

  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Civirules_Utils_HookInvoker();
    }
    return self::$singleton;
  }

  /**
   * hook_civirules_alter_delay_classes
   *
   * @param array $classes
   *
   * This hook could alter the classes with options for a delay
   */
  public function hook_civirules_alter_delay_classes($classes) {
    $this->invoke('civirules_alter_delay_classes', 1, ['classes'], $classes);
  }

  /**
   * hook_civicrm_civirules_logger
   *
   * @param \Psr\Log\LoggerInterface|NULL $logger
   *
   * This hook could set a logger class for Civirules
   */
  public function hook_civirules_getlogger(&$logger = null) {
    $this->invoke('civirules_logger', 1, ['logger'], $logger);
    if ($logger && !$logger instanceof \Psr\Log\LoggerInterface) {
      $logger = null;
    }
  }

  public function hook_civirules_alterTriggerData(CRM_Civirules_TriggerData_TriggerData &$triggerData) {
    $this->invoke('civirules_alter_trigger_data', 1, ['triggerData'], $triggerData);
  }

  private function invoke($fnSuffix, $numParams, $params = [], &$arg1 = null, &$arg2 = null, &$arg3 = null, &$arg4 = null, &$arg5 = null) {
    $hook =  CRM_Utils_Hook::singleton();
    $civiVersion = CRM_Core_BAO_Domain::version();

    if (version_compare($civiVersion, '4.5', '<')) {
      //in CiviCRM 4.4 the invoke function has 5 arguments maximum
      return $hook->invoke($numParams, $arg1, $arg2, $arg3, $arg4, $arg5, $fnSuffix);
    }
    elseif (version_compare($civiVersion, '5.28', '>=')) {
      return $hook->invoke($params, $arg1, $arg2, $arg3, $arg4, $arg5, CRM_Utils_Hook::$_nullObject, $fnSuffix);
    }
    else {
      return $hook->invoke($numParams, $arg1, $arg2, $arg3, $arg4, $arg5, CRM_Utils_Hook::$_nullObject, $fnSuffix);
    }
  }

}
