<?php

/**
 * Collection of upgrade steps
 */
class CRM_Kwartaalbijdrage_Upgrader extends CRM_Kwartaalbijdrage_Upgrader_Base {


  public function install() {
    $params['option_group_id'] = civicrm_api3('OptionGroup', 'getvalue', array('name' => 'activity_type', 'return' => 'id'));
    $params['name'] = 'leden_telling';
    $params['label'] = 'Leden telling';
    civicrm_api3('OptionValue', 'create', $params);
    $params['name'] = 'tribune_bezorging';
    $params['label'] = 'Tribune bezorging';
    civicrm_api3('OptionValue', 'create', $params);
    $params['name'] = 'kwartaal_bijdrage';
    $params['label'] = 'Kwartaal bijdrage';
    civicrm_api3('OptionValue', 'create', $params);

    $this->executeCustomDataFile('xml/ledentelling.xml');
    $this->executeCustomDataFile('xml/tribunebezorging.xml');
    $this->executeCustomDataFile('xml/kwartaalbijdrage.xml');
  }

  /**
   * Remove all kwartaal bijdrage activiteiten van 1 juli 2015
   * @return bool
   */
  public function upgrade_1001() {
    $kwartaal_bijdrage_activity = CRM_Core_OptionGroup::getValue('activity_type', 'kwartaal_bijdrage', 'name');
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_activity` WHERE `activity_type_id` = '".$kwartaal_bijdrage_activity."' AND DATE(`activity_date_time`) = '2015-07-01'");
    while($dao->fetch()) {
      $params = array('id' => $dao->id);
      CRM_Activity_BAO_Activity::deleteActivity($params);
    }
    return true;
  }



}
