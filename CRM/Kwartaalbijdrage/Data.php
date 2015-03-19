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
        $this->basisbedrag = $settings->getBasisbedragPerJaar() / 12;
        $this->ledenvergoeding_per_lid = $settings->getLedenvergoedingPerLid();
        $this->tribunevergoeding_per_tribune = $settings->getTribunevergoedingPerTribune();

        $this->afdeling_id = $afdeling_id;
        $this->date = new DateTime();
    }

    public function setAantalLeden($aantal_leden) {
        $this->aantal_leden = $aantal_leden;
        $this->ledenvergoeding = $this->aantal_leden * $this->ledenvergoeding_per_lid;

        $this->totaal_bijdrage = $this->basisbedrag + $this->ledenvergoeding + $this->tribunebezorging_vergoeding;
    }

    public function setAantalTribunes($aantal_tribunes) {
        $this->bezorgde_tribunes = $aantal_tribunes;
        $this->tribunebezorging_vergoeding = $this->bezorgde_tribunes * $this->tribunevergoeding_per_tribune;

        $this->totaal_bijdrage = $this->basisbedrag + $this->ledenvergoeding + $this->tribunebezorging_vergoeding;
    }

}