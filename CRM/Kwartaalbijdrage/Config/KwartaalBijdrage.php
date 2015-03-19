<?php

class CRM_Kwartaalbijdrage_Config_KwartaalBijdrage {

    private static $singleton;

    public $customGroup;

    public $date;

    public $basisbedrag;

    public $aantal_leden;

    public $ledenvergoeding;

    public $bezorgde_tribunes;

    public $tribunebezorging_vergoeding;

    public $totaal_bijdrage;

    public $ledenvergoeding_per_lid;

    public $tribunevergoeding_per_tribune;

    private function __construct() {
        $this->customGroup = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Kwartaalbijdrage'));
        $gid = $this->customGroup['id'];

        $this->date = civicrm_api3('CustomField', 'getsingle', array('name' => 'date', 'custom_group_id' => $gid));
        $this->basisbedrag = civicrm_api3('CustomField', 'getsingle', array('name' => 'basisbedrag', 'custom_group_id' => $gid));
        $this->aantal_leden = civicrm_api3('CustomField', 'getsingle', array('name' => 'aantal_leden', 'custom_group_id' => $gid));
        $this->ledenvergoeding = civicrm_api3('CustomField', 'getsingle', array('name' => 'ledenvergoeding', 'custom_group_id' => $gid));
        $this->bezorgde_tribunes = civicrm_api3('CustomField', 'getsingle', array('name' => 'bezorgde_tribunes', 'custom_group_id' => $gid));
        $this->tribunebezorging_vergoeding = civicrm_api3('CustomField', 'getsingle', array('name' => 'tribunebezorging_vergoeding', 'custom_group_id' => $gid));
        $this->totaal_bijdrage = civicrm_api3('CustomField', 'getsingle', array('name' => 'totaal_bijdrage', 'custom_group_id' => $gid));
        $this->ledenvergoeding_per_lid = civicrm_api3('CustomField', 'getsingle', array('name' => 'ledenvergoeding_per_lid', 'custom_group_id' => $gid));
        $this->tribunevergoeding_per_tribune = civicrm_api3('CustomField', 'getsingle', array('name' => 'tribunevergoeding_per_tribune', 'custom_group_id' => $gid));
    }

    /**
     * @return \CRM_Kwartaalbijdrage_Config_KwartaalBijdrage
     */
    public static function singleton() {
        if (!self::$singleton) {
            self::$singleton = new CRM_Kwartaalbijdrage_Config_KwartaalBijdrage();
        }
        return self::$singleton;
    }

}