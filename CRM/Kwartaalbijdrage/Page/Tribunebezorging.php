<?php

require_once 'CRM/Core/Page.php';

class CRM_Kwartaalbijdrage_Page_Tribunebezorging extends CRM_Core_Page {
  function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(ts('Tribunebezorging'));

    $this->assign('done', false);

    $action = CRM_Utils_Request::retrieve('action', 'String');
    if (!empty($action) && $action == CRM_Core_Action::ADD) {
      $return = CRM_Kwartaalbijdrage_Tribunebezorging::createTribunebezorgingActivity();
      $this->assign('done', true);
      $this->assign('afdelingen', count($return));
    }

    parent::run();
  }
}
