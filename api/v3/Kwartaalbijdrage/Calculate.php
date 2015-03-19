<?php

/**
 * Kwartaalbijdrage.Calculate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_kwartaalbijdrage_calculate($params) {
    if (!isset($params['month'])) {
        $now = new DateTime();
        $params['month'] = (int) $now->format('m');
    }

    if (array_key_exists('month', $params) && $params['month'] >= 1 AND $params['month'] <= 12) {
        $returnValues = array();
        $date = new DateTime();
        $date->setDate($date->format('Y'), $params['month'], 1);

        $returnValues = CRM_Kwartaalbijdrage_Calculate::getAllNotCalculatedAfdelingen($date);

        return civicrm_api3_create_success($returnValues, $params, 'Kwartaalbijdrage', 'Calculate');
    }
    else {
        throw new API_Exception('You should provide a month (between 1 and 12)');
    }
}

