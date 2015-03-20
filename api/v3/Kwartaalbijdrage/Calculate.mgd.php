<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Cron:Kwartaalbijdrage.Calculate',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Call Kwartaalbijdrage.Calculate API',
      'description' => 'Call Kwartaalbijdrage.Calculate API to update every month the kwartaalbijdrage voor afdelingen',
      'run_frequency' => 'Daily',
      'api_entity' => 'Kwartaalbijdrage',
      'api_action' => 'Calculate',
      'parameters' => '',
      'is_active' => '0',
    ),
  ),
);