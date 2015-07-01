<?php

class CRM_Kwartaalbijdrage_Kwartaalbijdrage {


  public static function createKwartaalbijdrage(DateTime $date) {
    $settings = CRM_Kwartaalbijdrage_Settings::singleton();

    $cg_kwartaalbijdrage = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'Kwartaalbijdrage', 'return' => 'id'));
    $cf_basisbedrag = civicrm_api3('CustomField', 'getvalue', array('name' => 'Basisbedrag', 'return' => 'id', 'custom_grooup_id' => $cg_kwartaalbijdrage));
    $cf_totaal_leden = civicrm_api3('CustomField', 'getvalue', array('name' => 'Totaal_leden', 'return' => 'id', 'custom_grooup_id' => $cg_kwartaalbijdrage));
    $cf_ledenvergoeding_per_lid = civicrm_api3('CustomField', 'getvalue', array('name' => 'Ledenvergoeding_per_lid', 'return' => 'id', 'custom_grooup_id' => $cg_kwartaalbijdrage));
    $cf_ledenvergeoding = civicrm_api3('CustomField', 'getvalue', array('name' => 'Ledenvergoeding', 'return' => 'id', 'custom_grooup_id' => $cg_kwartaalbijdrage));
    $cf_subtotaal_tribunebezorging = civicrm_api3('CustomField', 'getvalue', array('name' => 'Subtotaal_tribunebezorging', 'return' => 'id', 'custom_grooup_id' => $cg_kwartaalbijdrage));
    $cf_totaal_tribunebezorging = civicrm_api3('CustomField', 'getvalue', array('name' => 'Totaal_tribunebezorging', 'return' => 'id', 'custom_grooup_id' => $cg_kwartaalbijdrage));
    $cf_bezorgvergoeding_per_tribune = civicrm_api3('CustomField', 'getvalue', array('name' => 'Bezorgvergoeding_per_tribune', 'return' => 'id', 'custom_grooup_id' => $cg_kwartaalbijdrage));
    $cf_bezorgvergoeding = civicrm_api3('CustomField', 'getvalue', array('name' => 'Bezorgvergoeding', 'return' => 'id', 'custom_grooup_id' => $cg_kwartaalbijdrage));
    $cf_kwartaalbijdrage = civicrm_api3('CustomField', 'getvalue', array('name' => 'Kwartaalbijdrage', 'return' => 'id', 'custom_grooup_id' => $cg_kwartaalbijdrage));


    $kwartaal_bijdrage_activity = CRM_Core_OptionGroup::getValue('activity_type', 'kwartaal_bijdrage', 'name');

    //current quarter
    $q = ceil($date->format('m') / 3);
    $qStartDate = new DateTime();
    $qEndDate = new DateTime();
    switch ($q) {
      case '1':
        $qStartDate->setDate($date->format('Y'), 1, 1);
        $qEndDate->setDate($date->format('Y'), 3, 31);
        break;
      case '2':
        $qStartDate->setDate($date->format('Y'), 4, 1);
        $qEndDate->setDate($date->format('Y'), 6, 30);
        break;
      case '3':
        $qStartDate->setDate($date->format('Y'), 7, 1);
        $qEndDate->setDate($date->format('Y'), 9, 31);
        break;
      case '4':
        $qStartDate->setDate($date->format('Y'), 10, 1);
        $qEndDate->setDate($date->format('Y'), 12, 31);
        break;
    }

    //previous quarter
    $pqStartDate = clone $qStartDate;
    $pqStartDate->modify('-3 months');
    $pqEndDate = clone $qEndDate;
    $pqEndDate->modify('-3 months');

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
                    and civicrm_activity.activity_type_id = %1
                    and DATE(civicrm_activity.activity_date_time) >= DATE(%2)
                    and DATE(civicrm_activity.activity_date_time) <= DATE(%3)
                )";
    $params[1] = array($kwartaal_bijdrage_activity, 'Integer');
    $params[2] = array($qStartDate->format('Y-m-d'), 'String');
    $params[3] = array($qEndDate->format('Y-m-d'), 'String');
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    $return = array();
    while($dao->fetch()) {
      if (self::hasMoederAfdeling($dao->id) || CRM_Kwartaalbijdrage_Utils::isAfdelingOpgeheven($dao->id)) {
        continue;
      }

      $aantal_leden = self::getAantalLeden($dao->id, $pqStartDate, $pqEndDate);
      $aantal_tribunes = self::getAantalBezorgdeTribunes($dao->id, $pqStartDate, $pqEndDate);
      $kindAfdelingen = self::getKindAfdelingen($dao->id);
      foreach($kindAfdelingen as $kind_id) {
        $aantal_leden += self::getAantalLeden($kind_id, $pqStartDate, $pqEndDate);
        $aantal_tribunes += self::getAantalBezorgdeTribunes($kind_id, $pqStartDate, $pqEndDate);
      }

      $params = array();
      $params['activity_type_id'] = $kwartaal_bijdrage_activity;
      $params['status_id'] = 2; //completed
      $params['activity_date_time'] = $qStartDate->format('YmdHis');
      $params['target_contact_id'] = $dao->id;
      $params['subject'] = ts('Kwartaalbijdrage');
      $params['custom_'.$cf_basisbedrag] = $settings->getBasisbedrag();
      $params['custom_'.$cf_totaal_leden] = $aantal_leden;
      $params['custom_'.$cf_ledenvergoeding_per_lid] = $settings->getLedenvergoedingPerLid();
      $params['custom_'.$cf_ledenvergeoding] = $settings->getLedenvergoedingPerLid() * $aantal_leden;
      $params['custom_'.$cf_subtotaal_tribunebezorging] = $aantal_tribunes;
      $params['custom_'.$cf_totaal_tribunebezorging] = $aantal_tribunes - $aantal_leden;
      $params['custom_'.$cf_bezorgvergoeding_per_tribune] = $settings->getTribunevergoedingPerTribune();
      $params['custom_'.$cf_bezorgvergoeding] = $settings->getTribunevergoedingPerTribune() * $aantal_tribunes;
      $params['custom_'.$cf_kwartaalbijdrage] = $params['custom_'.$cf_basisbedrag] + $params['custom_'.$cf_ledenvergeoding] + $params['custom_'.$cf_bezorgvergoeding];
      civicrm_api3('Activity', 'create', $params);

      $return[] = array(
        'afdeling_id' => $dao->id,
        'afdeling' => $dao->display_name,
        'basisbedrag' => $params['custom_'.$cf_basisbedrag],
        'aantal_leden' => $params['custom_'.$cf_totaal_leden],
        'ledenvergoeding_per_lid' => $params['custom_'.$cf_ledenvergoeding_per_lid],
        'ledenvergoeding' => $params['custom_'.$cf_ledenvergeoding],
        'subtotaal_tribunes' => $params['custom_'.$cf_subtotaal_tribunebezorging],
        'totaal_tribunes' => $params['custom_'.$cf_totaal_tribunebezorging],
        'bezorgvergoeding_per_tribune' => $params['custom_'.$cf_bezorgvergoeding_per_tribune],
        'bezorgvergoeding' => $params['custom_'.$cf_bezorgvergoeding],
        'kwartaalbijdrage' => $params['custom_'.$cf_kwartaalbijdrage],
        'date' => $date->format('Y-m-d'),
      );
    }
    return $return;
  }

  /**
   * Bepaal of een afdeling een kind-afdeling is.
   *
   * @param $afdeling_id
   * @return bool
   */
  private static function hasMoederAfdeling($afdeling_id) {
    static $io_relationship = false;
    static $werkgroup_relationship = false;
    if (!$io_relationship) {
      $io_relationship = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'sprel_afdelingio_regio',
        'return' => 'id'
      ));
    }
    if (!$werkgroup_relationship) {
      $werkgroup_relationship = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Is werkgroep van',
        'return' => 'id'
      ));
    }
    $sql = "SELECT COUNT(*) FROM `civicrm_relationship` `r`
            WHERE (relationship_type_id = %1 OR r.relationship_type_id = %2) AND contact_id_a = %3
            AND is_active = 1
            AND (start_date IS NULL OR DATE(start_date) <= DATE(NOW()))
            AND (end_date IS NULL OR DATE(end_date) >= DATE(NOW()))";
    $params[1] = array($io_relationship, 'Integer');
    $params[2] = array($werkgroup_relationship, 'Integer');
    $params[3] = array($afdeling_id, 'Integer');

    $count = CRM_Core_DAO::singleValueQuery($sql, $params);
    if ($count) {
      return true;
    }

    return false;
  }

  /**
   * Geef een lijst met alle kind afdelingen
   *
   * @param $afdeling_id
   * @return bool
   */
  private static function getKindAfdelingen($afdeling_id) {
    static $io_relationship = false;
    static $werkgroup_relationship = false;
    if (!$io_relationship) {
      $io_relationship = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'sprel_afdelingio_regio',
        'return' => 'id'
      ));
    }
    if (!$werkgroup_relationship) {
      $werkgroup_relationship = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Is werkgroep van',
        'return' => 'id'
      ));
    }
    $sql = "SELECT contact_id_a FROM `civicrm_relationship` `r`
            WHERE (relationship_type_id = %1 OR r.relationship_type_id = %2) AND contact_id_b = %3
            AND is_active = 1
            AND (start_date IS NULL OR DATE(start_date) <= DATE(NOW()))
            AND (end_date IS NULL OR DATE(end_date) >= DATE(NOW()))";
    $params[1] = array($io_relationship, 'Integer');
    $params[2] = array($werkgroup_relationship, 'Integer');
    $params[3] = array($afdeling_id, 'Integer');

    $return = array();
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while($dao->fetch()) {
      if (!in_array($dao->contact_id_a, $return)) {
        $return[] = $dao->contact_id_a;
      }
    }

    return $return;
  }

  /**
   * Returns the total delivered Tribunes in a certain period
   *
   * @param $afdeling_id
   * @param $qStartDate
   * @param $qEndDate
   * @return string
   * @throws \CiviCRM_API3_Exception
   */
  private static function getAantalBezorgdeTribunes($afdeling_id, $qStartDate, $qEndDate) {
    $tribunebezorging_activity = CRM_Core_OptionGroup::getValue('activity_type', 'tribune_bezorging', 'name');
    $cg_tribunebezorging = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Tribunebezorging'));
    $cf_aantal_bezorgd = civicrm_api3('CustomField', 'getvalue', array('name' => 'aantal_bezorgd', 'return' => 'column_name', 'custom_grooup_id' => $cg_tribunebezorging['id']));

    $sql = "SELECT SUM(`".$cf_aantal_bezorgd."`) AS aantal_leden
            FROM `civicrm_activity`
            INNER JOIN `".$cg_tribunebezorging['table_name']."` ON `".$cg_tribunebezorging['table_name']."`.entity_id = civicrm_activity.id
            INNER JOIN civicrm_activity_contact ON civicrm_activity.id = civicrm_activity_contact.activity_id
            where civicrm_activity.activity_type_id = %1
            and civicrm_activity_contact.record_type_id = 3
            AND civicrm_activity_contact.contact_id = %2
            AND DATE(civicrm_activity.activity_date_time) >= DATE(%3)
            AND DATE(civicrm_activity.activity_date_time) <= DATE(%4)";
    $params[1] = array($tribunebezorging_activity, 'Integer');
    $params[2] = array($afdeling_id, 'Integer');
    $params[3] = array($qStartDate->format('Y-m-d'), 'String');
    $params[4] = array($qEndDate->format('Y-m-d'), 'String');

    return CRM_Core_DAO::singleValueQuery($sql, $params);
  }

  /**
   * Returns the total members of a department in a certain period
   *
   * @param $afdeling_id
   * @param $qStartDate
   * @param $qEndDate
   * @return string
   * @throws \CiviCRM_API3_Exception
   */
  private static function getAantalLeden($afdeling_id, $qStartDate, $qEndDate) {
    $cg_ledentelling = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Ledentelling'));
    $cf_aantal_leden = civicrm_api3('CustomField', 'getvalue', array('name' => 'Aantal_leden', 'return' => 'column_name', 'custom_grooup_id' => $cg_ledentelling['id']));
    $leden_telling_activity = CRM_Core_OptionGroup::getValue('activity_type', 'leden_telling', 'name');
    $sql = "SELECT SUM(`".$cf_aantal_leden."`) AS aantal_leden
            FROM `civicrm_activity`
            INNER JOIN `".$cg_ledentelling['table_name']."` ON `".$cg_ledentelling['table_name']."`.entity_id = civicrm_activity.id
            INNER JOIN civicrm_activity_contact ON civicrm_activity.id = civicrm_activity_contact.activity_id
            where civicrm_activity.activity_type_id = %1
            and civicrm_activity_contact.record_type_id = 3
            AND civicrm_activity_contact.contact_id = %2
            AND DATE(civicrm_activity.activity_date_time) >= DATE(%3)
            AND DATE(civicrm_activity.activity_date_time) <= DATE(%4)";
    $params[1] = array($leden_telling_activity, 'Integer');
    $params[2] = array($afdeling_id, 'Integer');
    $params[3] = array($qStartDate->format('Y-m-d'), 'String');
    $params[4] = array($qEndDate->format('Y-m-d'), 'String');

    return CRM_Core_DAO::singleValueQuery($sql, $params);
  }

}