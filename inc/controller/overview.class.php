<?php

/**
 *
 */
class overviewController extends watcherController {

    public function indexAction() {

        parent::indexAction();

        $time = time();

        //$overview = $this->livestatusModel->getOverview();
        //$this->smarty->assign("overview", $overview);
        $this->smarty->assign("operation", false);
        $this->smarty->assign("last_check", date(DATE_SOVA_DATETIME, $time));

        if (($alert = $this->_processCriticalSovaServices())) {
            $this->smarty->assign("alert", $alert);
        }
	
        $this->smarty->assign('pageTitle', 'ПНО');

        $content = $this->smarty->fetch("overview.tpl");
        $this->smarty->assign('content', $content);
        $this->smarty->display("index.tpl");

        $_SESSION["livestatus"]["lastcheck"] = $time;
    }

    public function operationAction() {
        $time = time();
        $this->overview = $this->livestatusModel->getOverview(null, false);

        $this->smarty->assignByRef("overview", $this->overview);
        $this->smarty->assign("operation", true);
        $this->smarty->assign("last_check", date(DATE_SOVA_DATETIME, $time));

        if (($alert = $this->_processCriticalSovaServices())) {
            $this->smarty->assign("alert", $alert);
        }

        $this->smarty->assign('pageTitle', 'Спеціальні');

	    $content = $this->smarty->fetch("overview.tpl");
        $this->smarty->assign('content', $content);
        $this->smarty->display("index.tpl");

        $_SESSION["livestatus"]["lastcheck"] = $time;
    }

    /*public function servicesAction() {
        $services = $this->livestatusModel->getServicesFull();
        echo "<pre>" . print_r($services, true) . "</pre>";
    }

    public function contactsAction() {
        $contacts = $this->livestatusModel->getContactsFull();
        echo "<pre>" . print_r($contacts, true) . "</pre>";
    }

    public function hostsAction() {
        $hosts = $this->livestatusModel->getHostsFull();
        echo "<pre>" . print_r($hosts, true) . "</pre>";
    }*/

}
