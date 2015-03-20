<?php

require_once 'CRM/Core/Page.php';

class CRM_Kwartaalbijdrage_Page_Kwartaalbijdrage extends CRM_Core_Page {

    protected $_contactId;

    function run() {
        CRM_Utils_System::setTitle(ts('Kwartaalbijdrage'));

        $config = CRM_Kwartaalbijdrage_Config_KwartaalBijdrage::singleton();

        $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Integer', $this, TRUE);
        $sql = "SELECT *
            FROM `" . $config->customGroup['table_name'] . "`
            WHERE `entity_id` = %1
            ORDER BY `".$config->date['column_name']."` DESC, `id` DESC";
        $params[1] = array($this->_contactId, 'Integer');

        $dao = CRM_Core_DAO::executeQuery($sql, $params);

        $datas = array();
        while($dao->fetch()) {
            $data = CRM_Kwartaalbijdrage_Data::setFromDao($dao);
            $datas[] = $data;
        }
        $this->assign('kwartaalbijdrages', $datas);
        parent::run();
    }
}
