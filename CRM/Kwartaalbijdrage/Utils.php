<?php

class CRM_Kwartaalbijdrage_Utils {

  protected static $opgeheven_tag_id = false;

  /**
   * Checks if a Afdeling has the tag Opgeheven
   *
   * @param $contact_id
   * @return bool
   */
  public static function isAfdelingOpgeheven($contact_id) {
    if (!self::$opgeheven_tag_id) {
      self::$opgeheven_tag_id = civicrm_api3('Tag', 'getvalue', array('name' => 'Opgeheven', 'return' => 'id'));
    }

    try {
      $params = array();
      $params['entity_table'] = 'civicrm_contact';
      $params['entity_id'] = $contact_id;
      $tags = civicrm_api3('EntityTag', 'get', $params);
      foreach($tags['values'] as $tag) {
        if ($tag['tag_id'] == self::$opgeheven_tag_id) {
          return true;
        }
      }
    } catch (Exception $e) {
      //do nothing
    }

    return false;
  }

}