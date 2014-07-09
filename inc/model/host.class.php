<?php

/**
 * This file contain hostModel class.
 * This class contain method for operating any user related data in DB.
 *
 *
 * @copyright  2010
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */

include_once (INC_PATH . 'model/dataType/host.php');

/**
 *
 */
class hostModel extends nagiosObjectModel
{
    const LOG_LIMIT = 255;

    protected $tableName = 'host';
    protected $primaryKey = 'host_id';

    protected $childs = array('contact', 'company');

    protected $_shouldLoadServices = true;

    public function __construct($dbms = NULL)
    {

        $this->addField(new hostId($this));
        $this->addField(new hostName($this));
        $this->addField(new hostNagiosName($this));
        $this->addField(new hostType($this));
        $this->addField(new hostNagvisMapName($this));
        $this->addField(new hostDescription($this));
        $this->addField(new hostAlias($this));
        $this->addField(new hostSendSosEvent($this));

        $this->addField(new hostIcon($this));

        $this->addField(new hostLocation($this));
        //$this->addField(new hostSearchSystemCode($this));
        $this->addField(new hostObjectId($this));
        $this->addField(new hostLinkerId($this));
        $this->addField(new hostIsOnService($this));
        $this->addField(new hostSchemeImage($this));
        //$this->addField(new hostObjectTypeId($this));
        //$this->addField(new hostEmergencyGroupId($this));
        //$this->addField(new hostEntrancePositionSystem($this));
        //$this->addField(new hostEntrancePositionLongitude($this));
        //$this->addField(new hostEntrancePositionLatitude($this));
        //$this->addField(new hostContactLegalId($this));
        //$this->addField(new hostEdrpou($this));
        //$this->addField(new hostInn($this));
        //$this->addField(new hostDk009_96($this));
        //$this->addField(new hostDk002_2004($this));
        //$this->addField(new hostCentralAuthority($this));
        //$this->addField(new hostLocalAuthority($this));
        $this->addField(new hostPassport($this));
        $this->addField(new hostConfigInfo($this));

        return parent::__construct($dbms);
    }

    public function getAll()
    {
        $data = parent::getAll();

        if (is_array($data)) {
            $keys = array();
            foreach ($data as $key => $value) {
                $keys[$key] = hashKey($value["nagios_name"]);
            }
            $data = array_combine($keys, array_values($data));
        }
        return $data;
    }

    public function loadContacts($key = NULL)
    {
        if (!$this->assureLoaded($key)) {
            return $this;
        }

        $contactModel = new contactModel();
        $this->data["contacts"] = $contactModel->getByHostId($this->getId());

        $companyModel = new companyModel();
        $this->data["companies"] = $companyModel->getByHostId($this->getId());

        return $this;
    }

    public function loadZones($key = NULL)
    {
        if (!$this->assureLoaded($key)) {
            return $this;
        }

        $zoneModel = new zoneModel();
        $this->data['zones'] = $zoneModel->getByHostId($this->getId());

        foreach ($this->data['zones'] as $zone) {
            if ($zone['entrance_position_longitude'] && $zone['entrance_position_latitude']) {
                $this->data['has_geo'] = true;
                break;
            }
        }

        return $this;
    }

    public function loadChannels($key = NULL)
    {
        if (!$this->assureLoaded($key)) {
            return $this;
        }

        $channelModel = new channelModel();
        $this->data["channels"] = $channelModel->getByHostId($this->getId());

        return $this;
    }

    public function loadRegistry($key = NULL)
    {
        if (!$this->assureLoaded($key)) {
            return $this;
        }

        $registryModel = new hostRegistryModel();
        $this->data["registry"] = $registryModel->getByHostId($this->getId());

        return $this;
    }

    public function loadServices($key = NULL)
    {
        if (!$this->assureLoaded($key)) {
            return $this;
        }

        $serviceModel = new serviceModel();
        $this->data["services"] = $serviceModel->getByHost($this);

        return $this;
    }

    public function loadCommunicationDevices($key = NULL)
    {
        if (!$this->assureLoaded($key)) {
            return $this;
        }

        if (!isset($this->data["communication_devices"])) {
            $model = new communicationDeviceModel();
            $this->data["communication_devices"] = $model->getByHost($this);
        }

        return $this;
    }

    public function loadNotificationDevices($key = NULL)
    {
        if (!$this->assureLoaded($key)) {
            return $this;
        }

        if (!isset($this->data["notification_devices"])) {
            $model = new notificationDeviceModel();
            $this->data["notification_devices"] = $model->getByHost($this);
        }

        return $this;
    }

    protected function _loadNagiosData()
    {
        $this->data["nagios"] = $this->livestatusModel->getOverview($this->data["nagios_name"], $this->_shouldLoadServices);
        $this->associateServices();
        return $this;
    }

    public function setLoadServices($option)
    {
        $this->_shouldLoadServices = $option;
        $this->associateServices();
        return $this;
    }

    public function associateServices()
    {
        if (isset($this->data['services']) && $this->data['services']
            && isset($this->data['nagios']['services']) && $this->data['nagios']['services']
        ) {
            foreach ($this->data['nagios']['services'] as $key => $service) {
                if (isset($this->data['services'][$key]) && is_array($this->data['services'][$key])) {
                    $this->data['nagios']['services'][$key]['db_data'] = & $this->data['services'][$key];
                }
            }
        }

        return $this;
    }

    public function loadNagiosLog($filters = NULL, $key = NULL)
    {
        if (!$this->assureLoaded($key)) {
            return $this;
        }

        //$from = isset($filters['time']) ? $filters['time'] : mktime(0, 0, 0) - 86400;
        $from = isset($filters['time']) ? $filters['time'] : 0;
        $to = isset($filters['time_end']) ? $filters['time_end'] : time();

        $nagiosLog = new nagiosLogModel();

        $nagiosLog->setFilter('time', '>=', $from)
            ->setFilter('time', '<', $to)
            ->setFilter('host_id', '=', $this->data['host_id']);

        if (!empty($filters['state'])) {
            if (is_array($filters['state'])) {
                if (count($filters['state']) > 1) {
                    $nagiosLog->setFilter('state', 'IN', $filters['state']);
                } else {
                    $nagiosLog->setFilter('state', '=', $filters['state'][0]);
                }
            } else {
                $nagiosLog->setFilter('state', '=', $filters['state']);
            }
        }

        if (!empty($filters['plugin_output'])) {
            $nagiosLog->setFilter('plugin_output', 'LIKE', "%{$filters['plugin_output']}%");
        }

        if (!empty($filters['service_description'])) {
            $nagiosLog->setFilter('service_description', 'LIKE', "%{$filters['service_description']}%");
        }

        if (!empty($filters['service_notes'])) {
            $nagiosLog->setFilter('service_notes', 'LIKE', "%{$filters['service_notes']}%");
        }

        $nagiosLog->pageSize = static::LOG_LIMIT;

        if (isset($filters['page']) && is_numeric($filters['page']) && $filters['page'] > 0) {
            $nagiosLog->currentPage = $filters['page'];
        }

        ini_set('memory_limit', '512M');
        $this->data['nagiosLog'] = $nagiosLog->getLog();
        $this->data['nagiosLogPageSize'] = $nagiosLog->pageSize;
        $this->data['nagiosLogCurrentPage'] = $nagiosLog->currentPage;
        $this->data['nagiosLogTotalRows'] = $nagiosLog->getCount();

        $this->data['daily_chart'] = $nagiosLog->getLogDailyData();

        /*$this->importLog();

        $this->data['nagiosLog'] = $this->db->fetchAll(
            'SELECT * FROM `nagioslog` AS `n`
             WHERE `n`.`host_name`=:host_name AND `n`.`time` >= :from AND `n`.`time` <= :to ORDER BY `n`.`time` DESC'
                , array(':host_name' => $this->data['nagios_name'], ':from' => $from, ':to' => $to), true);

        foreach ($this->data['nagiosLog'] as $key=>$value) {
            $this->data['nagiosLog'][$key]['state_text'] = nagiosObjectModel::stateCaption($value['state']);
        }*/

        return $this;
    }

    public function importLog()
    {
        /*$lastTimestamp = $this->getMax('time');
        echo $lastTimestamp; die;

        $filters['state_type'] = 'HARD';
        $filters['type'] = mklivestatusModel::NAGIOS_LOG_TYPE_SERIVCE_ALERT;
        $filters['time'] = array($lastTimestamp, '>=');

        $data = $this->livestatusModel->getLog($filters);

        foreach ($data as $value) {
            //$this->insert($value, INSERT_DELAYED | INSERT_IGNORE);
            $stmt = $this->db->prepare("INSERT DELAYED IGNORE INTO `nagioslog`
                (`time`, `host_name`, `service_description`, `message`, `plugin_output`, `state`)
                VALUES (?, ?, ?, ?, ?, ?)");

            try {
                $stmt->execute(array($data['time'], $data['host_name'], $data['service_description'], $data['message'], $data['plugin_output'], $data['state']));
            } catch (PDOException $e) {
                basicController::logError($e);
            }
        }*/
    }

    /*public function viewlog() {
	//$lastTimestamp = $this->getMax('time');
	$filters['state_type'] = 'HARD';
        $filters['type'] = mklivestatusModel::NAGIOS_LOG_TYPE_SERIVCE_ALERT;
        $filters['time'] = array(1314369809, '>=');

        $data = $this->livestatusModel->getLog($filters);
        var_dump($data);die;
    }*/

    public function importFromNagios($data)
    {
        return false;
    }


    protected function _install()
    {
    }

}