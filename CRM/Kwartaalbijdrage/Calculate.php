<?php
/**
 * Created by PhpStorm.
 * User: jaap
 * Date: 3/19/15
 * Time: 2:40 PM
 */

class CRM_Kwartaalbijdrage_Calculate {

    public static function calculate(DateTime $date, $afdeling_id) {
        $data = new CRM_Kwartaalbijdrage_Data($afdeling_id);
        $data->date = $date;

        $data->setAantalLeden(self::calculateLeden($date, $afdeling_id));

        $aantal_tribunes = 0;
        if (!self::hasPalletAdres($afdeling_id)) {
            $aantal_tribunes = self::calculateTribune($date, $afdeling_id);
        }
        $pallets = self::getTribunePalletAdresses($afdeling_id);
        foreach($pallets as $pallet_id) {
            $aantal_tribunes = $aantal_tribunes + self::calculateTribune($date, $pallet_id);
        }

        $data->setAantalTribunes($aantal_tribunes);
    }

    protected static function calculateTribune(DateTime $date, $afdeling_id) {
        $mconfig = CRM_Kwartaalbijdrage_Config_TribuneMembershipTypes::singleton();
        $bcconfig = CRM_Bezorggebieden_Config_BezorggebiedContact::singleton();
        $bconfig = CRM_Bezorggebieden_Config_Bezorggebied::singleton();

        $sql = "SELECT count(distinct c.id)
                FROM civicrm_contact c
                INNER JOIN civicrm_membership m on c.id = m.contact_id
                INNER JOIN `".$bcconfig->getCustomGroupBezorggebiedContact('table_name')."` `bc` ON c.id = bc.entity_id
                INNER JOIN `".$bconfig->getCustomGroup('table_name')."` b ON b.id = bc.`".$bcconfig->getCustomFieldBezorggebied('column_name')."`
                WHERE b.`entity_id` = %1
                AND
                m.membership_type_id IN (".implode(", ", $mconfig->getMembershipTypeIds()).")
                AND (DATE(m.start_date) <= DATE(%2) OR m.start_date IS NULL)
                AND (DATE(m.end_date) >= DATE(%2) OR m.end_date IS NULL)
                AND (c.deceased_date >= DATE(%2) OR c.decaesed_date IS NULL)
              ";

        $params[1] = array($afdeling_id, 'Integer');
        $params[2] = array($date->format('Y-m-d'), 'String');

        $aantal_tribunes = CRM_Core_DAO::singleValueQuery($sql, $params);

        return $aantal_tribunes;
    }

    protected static function hasPalletAdres($afdeling_id) {
        $tribune_adres = CRM_Bezorggebieden_Config_TribuneAdres::singleton();

        $sql = "SELECT master_id
                FROM `civicrm_address`
                WHERE contact_id = %1 AND location_type_id = %2";
        $params[1] = array($afdeling_id, 'Integer');
        $params[2] = array($tribune_adres->tribune_adres_id, 'Integer');
        $dao = CRM_Core_DAO::executeQuery($sql, $params);
        if ($dao->fetch() && !empty($dao->master_id)) {
            return true;
        }
        return false;
    }

    protected static function getTribunePalletAdresses($afdeling_id) {
        $tribune_adres = CRM_Bezorggebieden_Config_TribuneAdres::singleton();

        $sql = "SELECT pallet.contact_id
                FROM `civicrm_address` `master`
                INNER JOIN `civicrm_address` `pallet` ON `master`.id = `pallet`.`master_id`
                WHERE master.contact_id = %1 AND master.location_type_id = %2 AND pallet.location_type_id = %2";
        $params[1] = array($afdeling_id, 'Integer');
        $params[2] = array($tribune_adres->tribune_adres_id, 'Integer');
        $dao = CRM_Core_DAO::executeQuery($sql, $params);
        $pallet_afdelingen = array();
        while($dao->fetch()) {
            $pallet_afdelingen[] = $dao->contact_id;
        }
        return $pallet_afdelingen;
    }

    protected static function calculateLeden(DateTime $date, $afdeling_id) {
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
                AND (c.deceased_date >= DATE(%2) OR c.decaesed_date IS NULL)
              ";

        $params[1] = array($afdeling_id, 'Integer');
        $params[2] = array($date->format('Y-m-d'), 'String');

        $aantal_leden = CRM_Core_DAO::singleValueQuery($sql, $params);
        return $aantal_leden;
    }

}