<?php

class CRM_Kwartaalbijdrage_Ledentelling {

  /**
   * Count members of departments and create an activity with the total members on
   * a certain date
   *
   * @param \DateTime $date
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  public static function countAllNotCalculatedAfdelingen(DateTime $date) {
    $cfsp = CRM_Spgeneric_CustomField::singleton();
    $cg_ledentelling = $cfsp->getGroupId('Ledentelling');
    $cf_aantal_leden = $cfsp->getFieldId('Ledentelling', 'Aantal_leden');
    $leden_telling_activity = CRM_Core_OptionGroup::getValue('activity_type', 'leden_telling', 'name');
    $sql = "SELECT c.id, c.display_name
                FROM `civicrm_contact` `c`
                WHERE c.contact_type = 'Organization'
                AND (
                  c.contact_sub_type LIKE '%" . CRM_Core_DAO::VALUE_SEPARATOR . "SP_Afdeling%'
                  OR c.contact_sub_type LIKE '%" . CRM_Core_DAO::VALUE_SEPARATOR . "SP_Werkgroep%'
                  )
                AND c.id NOT IN (
                    select civicrm_activity_contact.contact_id
                    from civicrm_activity_contact
                    inner join civicrm_activity on civicrm_activity.id = civicrm_activity_contact.activity_id
                    where civicrm_activity_contact.record_type_id = 3
                    and civicrm_activity.activity_type_id = %2
                    and MONTH(civicrm_activity.activity_date_time) = MONTH(%1)
                    and YEAR(civicrm_activity.activity_date_time) = YEAR(%1)
                )";
    $params[1] = array($date->format('Y-m-d'), 'String');
    $params[2] = array($leden_telling_activity, 'Integer');
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    $return = array();
    while($dao->fetch()) {
      if (CRM_Kwartaalbijdrage_Utils::isAfdelingOpgeheven($dao->id)) {
        continue;
      }

      $aantal_leden = self::ledentelling($date, $dao->id);

      $params = array();
      $params['activity_type_id'] = $leden_telling_activity;
      $params['status_id'] = 2; //completed
      $params['activity_date_time'] = $date->format('YmdHis');
      $params['target_contact_id'] = $dao->id;
      $params['subject'] = ts('Ledentelling');
      $params['custom_'.$cf_aantal_leden] = $aantal_leden;
      civicrm_api3('Activity', 'create', $params);

      $return[] = array(
        'afdeling_id' => $dao->id,
        'afdeling' => $dao->display_name,
        'aantal_leden' => $aantal_leden,
        'date' => $date->format('Y-m-d'),
      );
    }
    return $return;
  }

  /**
   * This function counts the members on a certain date for a department
   *
   * @param \DateTime $date
   * @param $afdeling_id
   * @return string
   */
  protected static function ledentelling(DateTime $date, $afdeling_id) {
    $mconfig = CRM_Kwartaalbijdrage_Config_MembershipTypes::singleton();
    $gconfig = CRM_Geostelsel_Config::singleton();
    $sql = "SELECT count(distinct c.id)
                FROM civicrm_contact c
                INNER JOIN civicrm_membership m on c.id = m.contact_id
                INNER JOIN `".$gconfig->getGeostelselCustomGroup('table_name')."` `g` ON c.id = g.entity_id
                WHERE g.`".$gconfig->getAfdelingsField('column_name')."` = %1
                AND
                m.membership_type_id IN (".implode(", ", $mconfig->getMembershipTypeIds()).")
                AND (DATE(m.start_date) <= DATE(%2) OR m.start_date IS NULL)
                AND (DATE(m.end_date) >= DATE(%2) OR m.end_date IS NULL)
                AND (c.deceased_date >= DATE(%2) OR c.deceased_date IS NULL)
              ";
    $params[1] = array($afdeling_id, 'Integer');
    $params[2] = array($date->format('Y-m-d'), 'String');
    $aantal_leden = CRM_Core_DAO::singleValueQuery($sql, $params);
    return $aantal_leden;
  }

}