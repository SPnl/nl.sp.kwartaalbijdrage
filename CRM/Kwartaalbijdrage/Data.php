<?php

class CRM_Kwartaalbijdrage_Data {

    public $afdeling_id;

    public $date;

    public $basisbedrag;

    public $aantal_leden;

    public $ledenvergoeding;

    public $bezorgde_tribunes;

    public $tribunebezorging_vergoeding;

    public $totaal_bijdrage;

    public $ledenvergoeding_per_lid;

    public $tribunevergoeding_per_tribune;

    public function __construct($afdeling_id) {
        $settings = CRM_Kwartaalbijdrage_Settings::singleton();
        $this->basisbedrag = round($settings->getBasisbedragPerJaar() / 12, 2);
        $this->ledenvergoeding_per_lid = $settings->getLedenvergoedingPerLid();
        $this->tribunevergoeding_per_tribune = $settings->getTribunevergoedingPerTribune();

        $this->afdeling_id = $afdeling_id;
        $this->date = new DateTime();
    }

    public static function setFromDao($dao) {
        $config = CRM_Kwartaalbijdrage_Config_KwartaalBijdrage::singleton();

        $f_date = $config->date['column_name'];
        $f_basisbedrag = $config->basisbedrag['column_name'];
        $f_aantal_leden = $config->aantal_leden['column_name'];
        $f_ledenvergoeding = $config->ledenvergoeding['column_name'];
        $f_bezorgde_tribunes = $config->bezorgde_tribunes['column_name'];
        $f_tribunebezorging_vergoeding = $config->tribunebezorging_vergoeding['column_name'];
        $f_totaal_bijdrage = $config->totaal_bijdrage['column_name'];
        $f_ledenvergoeding_per_lid = $config->ledenvergoeding_per_lid['column_name'];
        $f_tribunevergoeding_per_tribune = $config->tribunevergoeding_per_tribune['column_name'];

        $data = new CRM_Kwartaalbijdrage_Data($dao->entity_id);

        $data->date = new DateTime($dao->$f_date);
        $data->basisbedrag = $dao->$f_basisbedrag;
        $data->aantal_leden = $dao->$f_aantal_leden;
        $data->ledenvergoeding = $dao->$f_ledenvergoeding;
        $data->bezorgde_tribunes = $dao->$f_bezorgde_tribunes;
        $data->tribunebezorging_vergoeding = $dao->$f_tribunebezorging_vergoeding;
        $data->totaal_bijdrage = $dao->$f_totaal_bijdrage;
        $data->ledenvergoeding_per_lid = $dao->$f_ledenvergoeding_per_lid;
        $data->tribunevergoeding_per_tribune = $dao->$f_tribunevergoeding_per_tribune;

        return $data;
    }

    public function setAantalLeden($aantal_leden) {
        $this->aantal_leden = $aantal_leden;
        $this->calculate();
    }

    public function setAantalTribunes($aantal_tribunes) {
        $this->bezorgde_tribunes = $aantal_tribunes;
        $this->calculate();
    }

    public function calculate() {
        $this->ledenvergoeding = round($this->aantal_leden * $this->ledenvergoeding_per_lid, 2);
        $this->tribunebezorging_vergoeding = round(($this->bezorgde_tribunes - $this->aantal_leden) * $this->tribunevergoeding_per_tribune, 2);
        $this->totaal_bijdrage = round($this->basisbedrag + $this->ledenvergoeding + $this->tribunebezorging_vergoeding, 2);
    }

    public function formattedDate() {
        return $this->date->format('d-m-Y');
    }

}