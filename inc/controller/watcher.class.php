<?php

/**
 * Special controller to watch over Nagios services
 */
abstract class watcherController extends basicController
{
    /** @var $livestatusModel mklivestatusModel */

    protected $livestatusModel;

    /** @var $alertModel alertModel */
    protected $alertModel;

    protected $services = array();
    protected $hosts = array();
    protected $databaseHosts = array();

    protected $overview = array();

    protected $_useLastCheck = false;

    public function __construct()
    {
        $this->livestatusModel = new mklivestatusModel();
        $this->alertModel = new alertModel();
        $this->livestatusModel->setContactFilterName($_SESSION["user"]["name"]);

        return parent::__construct();
    }

    public function indexAction()
    {
        $operator = $this->getCurrentOperator();

        if (isset($operator['settings']['display_host']) && $operator['settings']['display_host']) {
            $hostNames = array_keys($operator['settings']['display_host']);
        } else {
            $hostNames = null;
        }

        $this->overview = $this->livestatusModel->getOverview($hostNames, false);
        $this->smarty->assignByRef("overview", $this->overview);

        // get all
        $this->databaseHosts = $this->livestatusModel->getHosts();

        if ($this->databaseHosts) {
            $allowedHosts = array();
            foreach ($this->databaseHosts as $host) {
                $allowedHosts[] = $host['name'];
            }

            unset($host);

            $hostModel = new hostModel();
            $hostModel->setFilter('nagios_name', 'in', $allowedHosts);
            $this->databaseHosts = $hostModel->getAll();
        } else {
            $this->databaseHosts = array();
        }

        foreach ($this->overview as $key => $value) {
            if (isset($this->databaseHosts[$key])) {
                $this->overview[$key]['db_data'] = $this->databaseHosts[$key];
            }
        }

        $this->smarty->assignByRef('database_hosts', $this->databaseHosts);
    }

    abstract public function getRefreshUri();

    /**
     *
     */
    protected function _xajaxUpdateServiceTable($useLastcheck)
    {
        foreach ($this->services as $service) {
            $service["md5"] = hashKey($service["host_name"] . $service["description"]);
            // set service state class name
            $this->objResponse->assign("service" . $service["md5"], "className", "service bgServiceState{$service["state"]}");
            // set host image (icon)
            //$this->objResponse->assign("hostIcon" . md5($service["host_name"]), "src", LAYOUT_LOGOS_URL . mklivestatusModel::getIconWithStatus($service));
            // update service icons
            //$this->objResponse->script("$('li#service{$service["md5"]} img.imgAcknowledged')." . ($service["acknowledged"] ? 'removeClass' : 'addClass') . "('hidden');");
            //$this->objResponse->script("$('li#service{$service["md5"]} img.imgComments')." . (count($service["comments"]) ? 'removeClass' : 'addClass') . "('hidden');");
            $this->objResponse->script("$('li#service{$service["md5"]} img.imgFlapping')." . ($service["is_flapping"] ? 'removeClass' : 'addClass') . "('hidden');");
        }
    }

    protected function _updateServiceTable($useLastcheck)
    {
        $usedServiceFields = array(
            'md5' => null,
            'state' => null,
            'acknowledged' => null,
            'comments' => null,
            'is_flapping' => null,
            'host_name' => null,
            'description' => null
        );

        $keys = array();

        array_walk($this->services, function (&$service) use ($usedServiceFields, &$keys) {
            $keys[] = $service['md5'] = hashKey($service['host_name'] . $service['description']);
            $service = array_intersect_key($service, $usedServiceFields);
        });

        $this->jsonResponse->services =  array_combine($keys, $this->services);
    }

    /**
     * AJAX
     */
    public function xajaxRefreshStatuses($useLastcheck = true)
    {
        $time = time();
        $this->objResponse = new xajaxResponse();

        $lastCheck = $useLastcheck ? $_SESSION["livestatus"]["lastcheck"] : 0;
        $this->services = $this->livestatusModel->refreshServices($lastCheck);

        $this->_xajaxUpdateServiceTable($useLastcheck);

        $this->hosts = $this->livestatusModel->refreshHosts($lastCheck);
        /*
        foreach ($this->hosts as $host) {
            $host["md5"] = md5($host["name"]);
            // set host class name
            //$this->objResponse->assign("host" . $host["md5"], "className", "host bgHostState{$host["state"]}");
        }
        */

        // check for new critical services

        if (($alert = $this->_processCriticalSovaServices())) {
            $this->objResponse->script("openAlertWindow({$alert["alert_id"]})");
        }

        if ($this->services || $this->hosts) {
            $_SESSION["livestatus"]["lastcheck"] = $time;
            $this->objResponse->assign("sovaLastCheckValue", "innerHTML", date(DATE_SOVA_DATETIME, $time));
        }

        return $this->objResponse;
    }

    public function refreshStatusesAction()
    {
        $this->_useLastCheck = (boolean) isset($_POST['useLastCheck'])?$_POST['useLastCheck']:false;

        $time = time();
        $this->jsonResponse = new stdClass();

        $lastCheck = $this->_useLastCheck ? $_SESSION["livestatus"]["lastcheck"] : 0;
        $this->services = $this->livestatusModel->refreshServices($lastCheck);

        $this->_updateServiceTable($this->_useLastCheck);

        $this->hosts = $this->livestatusModel->refreshHosts($lastCheck);

        if ($this->services || $this->hosts) {
            $_SESSION["livestatus"]["lastcheck"] = $time;
            $this->jsonResponse->sovaLastCheckValue = date(DATE_SOVA_DATETIME, $time);
        }

        return $this->jsonResponse;
    }

    public function xajaxGetService($key)
    {
        $this->objResponse = new xajaxResponse();

        $service = new serviceModel();

        $data = $service->loadByHostIdAndNagiosName($key)->loadNagiosData()->getData();

        if (isset($data['nagios'][0]) && is_array($data['nagios'][0])) {
            $nagiosData = $data['nagios'][0];
            $key = $data['host_id'] . ':' . $nagiosData['description'];
            $this->objResponse->script("nagiosWatcher.setServiceData('$key', " .
                json_encode($data)
                . ")");
            /*$this->objResponse->script("console.log('$key', ".
                    json_encode($data).')');*/
        }

        return $this->objResponse;
    }

    protected function _processCriticalSovaServices()
    {
        // temporary disable alert processing
        return NULL;
        $criticalServices = $this->livestatusModel->getSovaCriticalServices($this->alertModel->getLastAlertTimestamp());

        $result = array();

        $hostsServices = array();

        foreach ($criticalServices as $service) {
            $service["md5"] = md5($service["host_name"] . $service["description"]);

            // get host data to add services states to alert
            if (!isset($hostsServices["host_name"])) {
                $hostsServices[$service["host_name"]] = $this->livestatusModel->getHostServices($service["host_name"]);
            }

            $data[":timestamp"] = $service["last_state_change"];
            $data[":state"] = $service["state"];
            $data[":host"] = $service["host_name"];
            $data[":service"] = $service["description"];
            $data[":message"] = $service["plugin_output"];

            $data[":services_data"] = serialize($hostsServices[$service["host_name"]]);

            $result = $this->alertModel->createAlert($data);
            if ($result instanceof PDOException) {
                self::logError($result);
            } else {
                //self::log("Added alert " . print_r($data, true));
            }
        }

        return $this->alertModel->getNewAlert($_SESSION["operator"]["operator_id"]);
    }

}
