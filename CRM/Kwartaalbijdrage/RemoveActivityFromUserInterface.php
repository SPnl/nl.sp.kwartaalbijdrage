<?php

class CRM_Kwartaalbijdrage_RemoveActivityFromUserInterface {

  public static function buildForm($formName, &$form) {
    if ($formName != 'CRM_Activity_Form_ActivityLinks' && $formName != 'CRM_Activity_Form_Activity') {
      return;
    }

    $kwartaal_bijdrage_activity = CRM_Core_OptionGroup::getValue('activity_type', 'kwartaal_bijdrage', 'name');
    $leden_telling_activity = CRM_Core_OptionGroup::getValue('activity_type', 'leden_telling', 'name');
    $tribunebezorging_activity = CRM_Core_OptionGroup::getValue('activity_type', 'tribune_bezorging', 'name');
    if ($formName == 'CRM_Activity_Form_ActivityLinks') {
      $activityTypes = $form->get_template_vars('activityTypes');

      unset($activityTypes[$kwartaal_bijdrage_activity]);
      unset($activityTypes[$leden_telling_activity]);
      unset($activityTypes[$tribunebezorging_activity]);

      $form->assign('activityTypes', $activityTypes);
    } elseif ($formName == 'CRM_Activity_Form_Activity') {
      $activityType = $form->_elements[$form->_elementIndex['activity_type_id']];
      foreach($activityType->_options as $k => $option) {
        switch ($option['attr']['value']) {
          case $kwartaal_bijdrage_activity:
          case $leden_telling_activity:
          case $tribunebezorging_activity:
            unset($activityType->_options[$k]);
            break;
        }
      }
    }
  }

}