<?php

class CRM_Kwartaalbijdrage_Tokens {

    private static $singelton;

    public static function tokens(&$tokens) {
        $tokens['kwartaalbijdrage']['kwartaalbijdrage.vorig_kwartaal_nr'] = 'Vorig kwartaal nr';
        $tokens['kwartaalbijdrage']['kwartaalbijdrage.vorig_kwartaal'] = 'Kwartaalbijdrage vorig kwartaal';
    }

    /**
     *
     * @return CRM_Kwartaalbijdrage_Tokens
     */
    public static function singleton() {
        if (!self::$singelton) {
            self::$singelton = new CRM_Kwartaalbijdrage_Tokens();
        }
        return self::$singelton;
    }

    public function tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
        if (!empty($tokens['kwartaalbijdrage'])) {
            if (in_array('vorig_kwartaal_nr', $tokens['kwartaalbijdrage']) || array_key_exists('vorig_kwartaal_nr', $tokens['kwartaalbijdrage'])) {
                $this->vorig_kwartaal_nr($values, $cids, $job, $tokens, $context);
            }
            if (in_array('vorig_kwartaal', $tokens['kwartaalbijdrage']) || array_key_exists('vorig_kwartaal', $tokens['kwartaalbijdrage'])) {
                $this->vorig_kwartaal($values, $cids, $job, $tokens, $context);
            }
        }
    }

    protected function vorig_kwartaal_nr(&$values, $cids, $job = null, $tokens = array(), $context = null) {
        $curQuarter = $this->getQuarter();
        foreach($cids as $cid) {
            $values[$cid]['kwartaalbijdrage.vorig_kwartaal_nr'] = $curQuarter;
        }
    }

    protected function vorig_kwartaal(&$values, $cids, $job = null, $tokens = array(), $context = null) {
        $startDate = new DateTime();
        switch($startDate->format('m')) {
            case 1:
            case 2:
            case 3:
                $startDate->setDate($startDate->format('Y'), 1, 1); //first day of current month;
                break;
            case 4:
            case 5:
            case 6:
                $startDate->setDate($startDate->format('Y'), 4, 1); //first day of current month;
                break;
            case 7:
            case 8:
            case 9:
                $startDate->setDate($startDate->format('Y'), 7, 1); //first day of current month;
                break;
            case 10:
            case 11:
            case 12:
                $startDate->setDate($startDate->format('Y'), 10, 1); //first day of current month;
                break;
        }

        //$startDate->modify('-3 month'); //previous quarter
        $endDate = clone $startDate;
        $endDate->modify('+3 month');
        $endDate->modify('-1 day');

        foreach($cids as $cid) {
            $values[$cid]['kwartaalbijdrage.vorig_kwartaal'] = $this->formatTotal($cid, $startDate, $endDate);
        }
    }

    protected function formatTotal($afdeling_id, $startDate, $endDate) {
        $month = array (
            1 => 'januari',
            2 => 'februari',
            3 => 'maart',
            4 => 'april',
            5 => 'mei',
            6 => 'juni',
            7 => 'juli',
            8 => 'augustus',
            9 => 'september',
            10 => 'oktober',
            11 => 'november',
            12 => 'december',
        );

        $config = CRM_Kwartaalbijdrage_Config_KwartaalBijdrage::singleton();
        $sql = "SELECT *
                FROM `".$config->customGroup['table_name']."`
                WHERE entity_id = %1
                AND DATE(`".$config->date['column_name']."`) >= DATE(%2)
                AND DATE(`".$config->date['column_name']."`) <= DATE(%3)
                ORDER BY `".$config->date['column_name']."`";
        $params[1] = array($afdeling_id, 'Integer');
        $params[2] = array($startDate->format('Y-m-d'), 'String');
        $params[3] = array($endDate->format('Y-m-d'), 'String');

        $return = "";

        $datas = array();
        $dao = CRM_Core_DAO::executeQuery($sql, $params);
        while($dao->fetch()) {
            $datas[] = CRM_Kwartaalbijdrage_Data::setFromDao($dao);
        }

        $return = "<style type=\"text/css\">
                    table.token_kwartaalbijdrage { border: none; width: 100%; }
                    table.token_kwartaalbijdrage .sub_total { font-weight: bold }
                    </style>
                    <table class=\"token_kwartaalbijdrage\">";
        $return .= "<tr><td>I</td><td>Basisbedrag</td><td></td><td></td><td></td></tr>";
        $totaal_basis = 0.00;
        foreach($datas as $data) {
            $totaal_basis += $data->basisbedrag;
            $return .= "<tr><td></td><td>".$month[(int) $data->date->format('m')]."</td><td></td><td>".CRM_Utils_Money::format($data->basisbedrag, 'EUR')."</td><td></td>";
        }
        $return .= "<tr><td></td><td></td><td></td><td></td><td class=\"sub_total\">".CRM_Utils_Money::format($totaal_basis, 'EUR')."</td></tr>";
        $return .= "</table>";

        return $return;
    }


    protected function getQuarter() {
        $now = new DateTime();
        $curMonth = date("m", time());
        $curQuarter = ceil($curMonth/3);
        $curQuarter - 1;
        if ($curQuarter < 1) {
            $curQuarter = 4;
        }
        return $curQuarter;
    }

}