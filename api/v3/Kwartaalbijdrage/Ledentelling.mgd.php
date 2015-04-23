<?php

return array (
  0 =>
    array (
      'name' => 'Cron:Kwartaalbijdrage.Ledentelling',
      'entity' => 'Job',
      'params' =>
        array (
          'version' => 3,
          'name' => 'Call Kwartaalbijdrage.Ledentelling API',
          'description' => 'Zet een activiteit op naam van de afdeling op de eerste dag van de maand met het aantal leden',
          'run_frequency' => 'Daily',
          'api_entity' => 'Kwartaalbijdrage',
          'api_action' => 'Ledentelling',
          'parameters' => '',
          'is_active' => '1',
        ),
    ),
);