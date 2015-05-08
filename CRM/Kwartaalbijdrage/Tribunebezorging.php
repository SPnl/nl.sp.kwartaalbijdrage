<?php

class CRM_Kwartaalbijdrage_Tribunebezorging {

  /**
   * Creates for every department/workgroup an activity with information on how many Tribunes they are delivering
   * how many Tribunes are send by post and how many tribunes are send from the printer the member
   *
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  public static function createTribunebezorgingActivity() {
    $tribunebezorging_activity = CRM_Core_OptionGroup::getValue('activity_type', 'tribune_bezorging', 'name');

    $cg_tribunebezorging = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'Tribunebezorging', 'return' => 'id'));
    $cf_aantal_bezorgd = civicrm_api3('CustomField', 'getvalue', array('name' => 'aantal_bezorgd', 'return' => 'id', 'custom_grooup_id' => $cg_tribunebezorging));

    $dao = CRM_Core_DAO::executeQuery("
                SELECT c.id, c.display_name
                FROM `civicrm_contact` `c`
                WHERE c.contact_type = 'Organization'
                AND (
                  c.contact_sub_type LIKE '%" . CRM_Core_DAO::VALUE_SEPARATOR . "SP_Afdeling%'
                  OR c.contact_sub_type LIKE '%" . CRM_Core_DAO::VALUE_SEPARATOR . "SP_Werkgroep%'
                  )"
    );

    $date = new DateTime();
    $return = array();
    while($dao->fetch()) {
      if (CRM_Kwartaalbijdrage_Utils::isAfdelingOpgeheven($dao->id)) {
        continue;
      }

      $aantal = CRM_Bezorggebieden_Utils_AfdelingTelling::getAfdelingTelling($dao->id);

      $params = array();
      $params['activity_type_id'] = $tribunebezorging_activity;
      $params['status_id'] = 2; //completed
      $params['activity_date_time'] = $date->format('YmdHis');
      $params['target_contact_id'] = $dao->id;
      $params['subject'] = ts('Tribunebezorging');
      $params['custom_'.$cf_aantal_bezorgd] = $aantal->getMemberCount();
      civicrm_api3('Activity', 'create', $params);

      $return[] = array(
        'afdeling_id' => $dao->id,
        'afdeling' => $dao->display_name,
        'aantal_bezorgd' => $aantal->getMemberCount(),
        'date' => $date->format('Y-m-d'),
      );
    }
    return $return;
  }

}