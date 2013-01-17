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

        if ($this->databaseHosts) {
            $zone = new zoneModel();

            array_walk($this->databaseHosts, function(& $host) use ($zone) {
                if (isset($host['host_id'])) {
                    $host['zones'] = $zone->getByHostId($host['host_id'], true);
                }
            });
        }

        $content = $this->smarty->fetch("welcome.tpl");
        $this->smarty->assign('content', $content);
        $this->smarty->assign('pageTitle', '');

        $this->smarty->display("index.tpl");

        $_SESSION["livestatus"]["lastcheck"] = $time;
    }
}
