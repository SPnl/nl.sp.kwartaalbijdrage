<?php

require_once 'kwartaalbijdrage.civix.php';

/**
 * Implementation of hook_civicrm_navigationMenu
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function kwartaalbijdrage_civicrm_navigationMenu( &$params ) {
    $maxKey = _kwartaalbijdrage_getMenuKeyMax($params);

    $parent =_kwartaalbijdrage_get_parent_id_navigation_menu($params, 'Memberships');

    $parent['child'][$maxKey+1] = array (
        'attributes' => array (
            "label"=> ts('Kwartaalbijdrage'),
            "name"=> ts('Kwartaalbijdrage'),
            "url"=> "civicrm/kwartaalbijdragen",
            "permission" => "access CiviMember",
            "parentID" => $parent['attributes']['navID'],
            "active" => 1,
        ),
        'child' => array(),
    );

    $parent['child'][$maxKey+2] = array (
      'attributes' => array (
        "label"=> ts('Tribunebezorging'),
        "name"=> ts('Tribunebezorging'),
        "url"=> "civicrm/tribunebezorging",
        "permission" => "access CiviMember",
        "parentID" => $parent['attributes']['navID'],
        "active" => 1,
      ),
      'child' => array(),
    );
}

function _kwartaalbijdrage_get_parent_id_navigation_menu(&$menu, $path, &$parent = NULL) {
    // If we are done going down the path, insert menu
    if (empty($path)) {
        return $parent;
    } else {
        // Find an recurse into the next level down
        $found = false;
        $path = explode('/', $path);
        $first = array_shift($path);
        foreach ($menu as $key => &$entry) {
            if ($entry['attributes']['name'] == $first) {
                if (!$entry['child']) $entry['child'] = array();
                $found = _kwartaalbijdrage_get_parent_id_navigation_menu($entry['child'], implode('/', $path), $entry);
            }
        }
        return $found;
    }
}

function _kwartaalbijdrage_getMenuKeyMax($menuArray) {
    $max = array(max(array_keys($menuArray)));
    foreach($menuArray as $v) {
        if (!empty($v['child'])) {
            $max[] = _kwartaalbijdrage_getMenuKeyMax($v['child']);
        }
    }
    return max($max);
}

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function kwartaalbijdrage_civicrm_config(&$config) {
  _kwartaalbijdrage_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function kwartaalbijdrage_civicrm_xmlMenu(&$files) {
  _kwartaalbijdrage_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function kwartaalbijdrage_civicrm_install() {
  _kwartaalbijdrage_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function kwartaalbijdrage_civicrm_uninstall() {
  _kwartaalbijdrage_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function kwartaalbijdrage_civicrm_enable() {
  _kwartaalbijdrage_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function kwartaalbijdrage_civicrm_disable() {
  _kwartaalbijdrage_civix_civicrm_disable();
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
function kwartaalbijdrage_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _kwartaalbijdrage_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function kwartaalbijdrage_civicrm_managed(&$entities) {
  _kwartaalbijdrage_civix_civicrm_managed($entities);
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
function kwartaalbijdrage_civicrm_caseTypes(&$caseTypes) {
  _kwartaalbijdrage_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function kwartaalbijdrage_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _kwartaalbijdrage_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
