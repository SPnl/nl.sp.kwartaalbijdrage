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

    $this->executeCustomDataFile('xml/ledentelling.xml');
  }




}
