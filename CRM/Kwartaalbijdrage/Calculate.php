<?php
/**
 * Created by PhpStorm.
 * User: jaap
 * Date: 3/19/15
 * Time: 2:40 PM
 */

class CRM_Kwartaalbijdrage_Calculate {

    public static function getAllNotCalculatedAfdelingen(DateTime $date) {
        $config = CRM_Kwartaalbijdrage_Config_KwartaalBijdrage::singleton();
        $sql = "SELECT c.id
                FROM `civicrm_contact` `c`
                WHERE c.contact_type = 'Organization'
                AND (
                  c.contact_sub_type LIKE '%" . CRM_Core_DAO::VALUE_SEPARATOR . "SP_Afdeling%'
                  OR c.contact_sub_type LIKE '%" . CRM_Core_DAO::VALUE_SEPARATOR . "SP_Werkgroep%'
                  )
                AND c.id NOT IN (
                    SELECT k.entity_id FROM `".$config->customGroup['table_name']."` `k`
                    WHERE DATE(`date`) = DATE(%1)
                )";
        $params[1] = array($date->format('Y-m-d'), 'String');
        $dao = CRM_Core_DAO::executeQuery($sql, $params);
        $return = array();
        while($dao->fetch()) {
            $data = self::calculate($date, $dao->id);
            self::saveData($data);
            $return[] = array(
                'afdeling_id' => $data->afdeling_id,
                'data' => $data,
            );
        }
        return $return;
    }

    public static function saveData(CRM_Kwartaalbijdrage_Data $data) {
        $config = CRM_Kwartaalbijdrage_Config_KwartaalBijdrage::singleton();

        $data->calculate();

        $params['id'] = $data->afdeling_id;
        $params['custom_'.$config->date['id']] = $data->date->format('Ymd');
        $params['custom_'.$config->basisbedrag['id']] = $data->basisbedrag;
        $params['custom_'.$config->aantal_leden['id']] = $data->aantal_leden;
        $params['custom_'.$config->ledenvergoeding['id']] = $data->ledenvergoeding;
        $params['custom_'.$config->bezorgde_tribunes['id']] = $data->bezorgde_tribunes;
        $params['custom_'.$config->tribunebezorging_vergoeding['id']] = $data->tribunebezorging_vergoeding;
        $params['custom_'.$config->totaal_bijdrage['id']] = $data->totaal_bijdrage;
        $params['custom_'.$config->ledenvergoeding_per_lid['id']] = $data->ledenvergoeding_per_lid;
        $params['custom_'.$config->tribunevergoeding_per_tribune['id']] = $data->tribunevergoeding_per_tribune;

        civicrm_api3('Contact', 'create', $params);
    }

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
        return $data;
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
                AND (c.deceased_date >= DATE(%2) OR c.deceased_date IS NULL)
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
                AND (c.deceased_date >= DATE(%2) OR c.deceased_date IS NULL)
              ";

        $params[1] = array($afdeling_id, 'Integer');
        $params[2] = array($date->format('Y-m-d'), 'String');

        $aantal_leden = CRM_Core_DAO::singleValueQuery($sql, $params);
        return $aantal_leden;
    }

}