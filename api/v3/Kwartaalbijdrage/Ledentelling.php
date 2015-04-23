<?php

function civicrm_api3_kwartaalbijdrage_ledentelling($params) {
  if (!isset($params['month'])) {
    $now = new DateTime();
    $params['month'] = (int) $now->format('m');
  }

  if (array_key_exists('month', $params) && $params['month'] >= 1 AND $params['month'] <= 12) {
    $returnValues = array();
    $date = new DateTime();
    $date->setDate($date->format('Y'), $params['month'], 1);

    $returnValues = CRM_Kwartaalbijdrage_Ledentelling::getAllNotCalculatedAfdelingen($date);

    return civicrm_api3_create_success($returnValues, $params, 'Kwartaalbijdrage', 'Ledentelling');
  }
  else {
    throw new API_Exception('You should provide a month (between 1 and 12)');
  }
}