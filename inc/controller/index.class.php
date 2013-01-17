<?php

/**
 *
 */
class indexController extends watcherController
{

    public function indexAction()
    {
        parent::indexAction();
        $time = time();

        $this->smarty->assign("last_check", date(DATE_SOVA_DATETIME, $time));

        if (($alert = $this->_processCriticalSovaServices())) {
            $this->smarty->assign("alert", $alert);
        }

        $content = $this->smarty->fetch("welcome.tpl");
        $this->smarty->assign('content', $content);
        $this->smarty->assign('pageTitle', '');

        $this->smarty->display("index.tpl");

        $_SESSION["livestatus"]["lastcheck"] = $time;
    }
}
