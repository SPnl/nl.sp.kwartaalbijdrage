<?php

class CRM_Kwartaalbijdrage_Config_MembershipTypes {

    private static $singleton;

    private $membership_type_ids = array();

    private function __construct() {
        $membership_types = array(
            'Lid SP',
            'Lid SP en ROOD',
            'Lid ROOD',
        );
        $sql = "SELECT id from civicrm_membership_type where name = %1";
        foreach($membership_types as $type) {
            $params = array(
                1 => array($type, 'String'),
            );
            $this->membership_type_ids[] = CRM_Core_DAO::singleValueQuery($sql, $params);
        }
    }

    /**
     * @return CRM_Kwartaalbijdrage_Config_MembershipTypes
     */
    public static function singleton() {
        if (!self::$singleton) {
            self::$singleton = new CRM_Kwartaalbijdrage_Config_MembershipTypes();
        }
        return self::$singleton;
    }

    public function getMembershipTypeIds() {
        return $this->membership_type_ids;
    }

}