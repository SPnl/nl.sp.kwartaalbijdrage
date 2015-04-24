<?php

class CRM_Kwartaalbijdrage_Settings {

    private $basisbedrag;

    private $ledenvergoeding_per_lid;

    private $tribunevergoeding_per_tribune;

    private function __construct() {
        $this->basisbedrag = CRM_Core_BAO_Setting::getItem('nl.sp.kwartaalbijdrage', 'basisbedrag', null, 1688.79);
        $this->ledenvergoeding_per_lid = CRM_Core_BAO_Setting::getItem('nl.sp.kwartaalbijdrage', 'ledenvergoeding_per_lid', null, 0.833);
        $this->tribunevergoeding_per_tribune = CRM_Core_BAO_Setting::getItem('nl.sp.kwartaalbijdrage', 'tribunevergoeding_per_tribune', null, 0.32);
    }

    /**
     * @return float
     */
    public function getBasisbedrag() {
        return (float) $this->basisbedrag;
    }

    /**
     * @return float
     */
    public function getLedenvergoedingPerLid() {
        return (float) $this->ledenvergoeding_per_lid;
    }

    /**
     * @return float
     */
    public function getTribunevergoedingPerTribune() {
        return (float) $this->tribunevergoeding_per_tribune;
    }

    /**
     * @param float $basisbedrag
     * @param float $ledenvergoeding
     * @param float $tribunevergoeding
     * @return CRM_Kwartaalbijdrage_Settings
     */
    public static function save($basisbedrag, $ledenvergoeding, $tribunevergoeding) {
        CRM_Core_BAO_Setting::setItem((float) $basisbedrag, 'nl.sp.kwartaalbijdrage', 'basisbedrag');
        CRM_Core_BAO_Setting::setItem((float) $ledenvergoeding, 'nl.sp.kwartaalbijdrage', 'ledenvergoeding_per_lid');
        CRM_Core_BAO_Setting::setItem((float) $tribunevergoeding, 'nl.sp.kwartaalbijdrage', 'tribunevergoeding_per_tribune');

        self::$singleton = new CRM_Kwartaalbijdrage_Settings();
        return self::$singleton;
    }

    /**
     * @var CRM_Kwartaalbijdrage_Settings
     */
    private static $singleton;

    /**
     * @return CRM_Kwartaalbijdrage_Settings
     */
    public static function singleton() {
        if (!self::$singleton) {
            self::$singleton = new CRM_Kwartaalbijdrage_Settings();
        }
        return self::$singleton;
    }

}