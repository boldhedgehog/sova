<?php

/**
 *
 */
class mapController extends basicController
{

    protected $databaseHosts = array();

    public function indexAction()
    {
        $hostModel = new hostModel();
        $hostModel->addJoin(
            "zoneModel",
            array("host_id"=>"host_id"),
            "zones",
            array('entrance_position_longitude', 'entrance_position_latitude')
        );
        $hostModel->setFilter('zones.entrance_position_longitude', 'not null', null);
        $hostModel->setFilter('zones.entrance_position_latitude', 'not null', null);
        $this->databaseHosts = $hostModel->getAll();

        if ($this->databaseHosts) {
            $zone = new zoneModel();

            array_walk($this->databaseHosts, function(& $host) use ($zone) {
                if (isset($host['host_id'])) {
                    $host['zones'] = $zone->getByHostId($host['host_id'], true);
                }
            });
        }

        $this->smarty->assignByRef('database_hosts', $this->databaseHosts);

        $content = $this->smarty->fetch("welcome.tpl");

        $this->smarty->assign('content', $content);
        $this->smarty->assign('pageTitle', '');

        $this->smarty->display('file:map-index.tpl');
    }
}
