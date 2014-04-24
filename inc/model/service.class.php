<?php

/**
 * This file contain serviceModel class.
 * This class contain method for operating any user related data in DB.
 *
 *
 * @copyright  2010
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */
class serviceId extends keyDatatype {

    protected $name = "service_id";

}

class serviceHostId extends keyDatatype {

    protected $name = "host_id";

}

class serviceName extends stringDatatype {

    protected $name = "name";

}

class serviceNagiosName extends stringDatatype {

    protected $name = "nagios_name";

}

class serviceAlias extends stringDatatype {

    protected $name = "alias";

}

class serviceSendSosEvent extends booleanDatatype {
    protected $name = "send_sos_event";
}

class serviceDescription extends keyDatatype {

    protected $name = "description";

}

class serviceZoneId extends keyDatatype {
    protected $name = "zone_id";
}

class serviceCommunicationDeviceId extends keyDatatype {
    protected $name = "communication_device_id";
}

class serviceType extends choiceDatatype
{
    protected $name = "type";
    protected $choices = array('service' => "Службовий", 'sensor' => "Датчик", 'button' => "Сухий Контакт");
}

class servicePosition extends integerDatatype {
    protected $name = "position";
}

class serviceSerialNumber extends stringDatatype
{
    protected $name = "serial_number";
}

class serviceLogicalNumber extends stringDatatype
{
    protected $name = "logical_number";
}

class serviceSchemeImage extends imageDatatype
{
    protected $name = "scheme_image_name";
    protected $caption = "Схема";
}
/**
 *
 * @author ISM-UKRAINE\a.yegorov
 *
 */
class serviceModel extends nagiosObjectModel {

    protected $tableName = "service";
    protected $primaryKey = "service_id";

    public function __construct($dbms = NULL) {

        $this->addField(new serviceId($this));
        $this->addField(new serviceCommunicationDeviceId($this));
        $this->addField(new serviceNagiosName($this));
        $this->addField(new serviceDescription($this));
        $this->addField(new serviceAlias($this));
        $this->addField(new serviceSendSosEvent($this));
        $this->addField(new serviceZoneId($this));
        $this->addField(new serviceType($this));
        $this->addField(new servicePosition($this));
        $this->addField(new serviceSerialNumber($this));
        $this->addField(new serviceLogicalNumber($this));

        $this->resetJoins()
            ->addLeftJoin("zoneModel", array("zone_id"=>"zone_id"), "zone")
            ->addLeftJoin("communicationDeviceModel", array("communication_device_id"=>"communication_device_id"), "communication_device")
            ->addLeftJoin("sensorTypeModel", array("sensor_type_id"=>"sensor_type_id"), "sensor_type")
            ;

        return parent::__construct($dbms);
    }

    /**
     *
     * @param integer $id
     * @param string $name
     */
    public function loadByHostIdAndNagiosName($id, $name = '') {
        if (strpos($id, ':')) {
            list ($id, $name) = explode(':', $id);
        }
        if ($name == '' || $id == '')
            return $this;
        $this->resetFilters();
        $this->setFilter("host_id", "=", $id);
        $this->setFilter("nagios_name", "=", $name);

        $data = $this->getAll();

        if (is_array($data) && count($data) > 0) {
            $this->data = $data[0];
            // set host_id, if it's empty from GET
            if (!isset($this->data["host_id"])) {
                $this->data["host_id"] = $id;
            }
        } else {
            $this->data = NULL;
        }

        return $this;
    }

    public function getByHost(hostModel $host) {
        if (!$host->getId()) {
            return NULL;
        }

        $hostData = $host->getData();
        $data = $this->resetFilters()
            ->resetOrders()
            ->setOrder('alias', self::ORDER_ASC)
            ->setFilter('host_id', '=', $host->getId())
            ->getAll();

        $keys = array();
        foreach ($data as $value) {
            $keys[] = hashKey($hostData['nagios_name'] . $value['nagios_name']);
        }

        return (is_array($data) && $data && $keys) ? array_combine($keys, $data) : array();
    }

    protected function _loadNagiosData() {
        $this->loadHost();
        
        $this->data["nagios"] = $this->livestatusModel->getServicesFull($this->data["nagios_name"], $this->data['host']['nagios_name']);
        
        return $this;
    }

    public function loadCards($key = NULL) {
        if (!$this->assureLoaded($key)) {
            return $this;
        }

        $alertCardModel = new alertCardModel();
        $this->data["cards"] = $alertCardModel->getByServiceId($this->getId());

        return $this;
    }

    public function loadHost($key = NULL) {
        if (!$this->assureLoaded($key)) {
            return $this;
        }

        // host is already loaded
        if (isset($this->data["host"]) && is_array($this->data["host"])) {
            return $this;
        }

        // host_id is not set for some reason
        if (!isset($this->data["host_id"])) {
            return $this;
        }

        $hostModel = new hostModel($this->dbms);
        $this->data["host"] = $hostModel->load($this->data["host_id"])->setLoadServices(false)->loadNagiosData()->getData();

        return $this;
    }
    
    public function getName()
    {
        return isset($this->data['position']) ?
            $this->data['position'] .'/'. $this->data['communication_device']['logical_number']
            .':' . $this->data['sensor_type']['name'] .' '. $this->data['alias']
            : $this->getData('alias');
        ;
    }

    protected function _assureHostLoaded($key = NULL) {
        $this->loadHost($key);

        return (isset($this->data["host"]) && is_array($this->data["host"]));
    }

    public function loadNagiosLog($filters = array(), $key = NULL) {
        if (!$this->_assureHostLoaded($key)) {
            return $this;
        }

        //ini_set('memory_limit','512M');
        //print_r($this->data);die;

        // select for this host only
        $hostName = isset($this->data["host"]["nagios_name"])?$this->data["host"]["nagios_name"]:null;
        if (!$hostName) {
            return $this;
        }

        $filters['service_description'] = array("field" => "service_description", "op" => "=", "value" => $this->data["nagios_name"]);
        $filters['host_name'] = array("field" => "host_name", "op" => "=", "value" => $hostName);
        // hard states only
        $filters['state_type'] = array("field" => "state_type", "op" => "=", "value" => "HARD");

        // for the frontend, select services only
        $filters['type'] = array("field" => "type", "op" => "=", "value" => mklivestatusModel::NAGIOS_LOG_TYPE_SERVICE_ALERT);

        return parent::loadNagiosLog($filters);
    }

    public function getDailyStateData($key = null) {
        if (!$this->assureLoaded($key)) {
            return false;
        }

        $query = 'SELECT HOUR(FROM_UNIXTIME(`time`)) AS `hour`,
COUNT(IF(`state` = 0, 1, NULL)) AS `OK`,
COUNT(IF(`state` = 1, 1, NULL)) AS `WARNING`,
COUNT(IF(`state` = 2, 1, NULL)) AS `CRITICAL`,
COUNT(IF(`state` = 3, 1, NULL)) AS `UNKNOWN`
/* , COUNT(1) AS `TOTAL` */
FROM `nagioslog` WHERE `service_id` = :service_id AND `host_id` = :host_id
GROUP BY HOUR(FROM_UNIXTIME(`time`))
ORDER BY HOUR(FROM_UNIXTIME(`time`))  ASC;';

        $result =  $this->getDb()->fetchAll(
            $query,
            array(
                'service_id' => (int)$this->data['service_id'],
                'host_id' => (int)$this->data['host_id']
            )
        );
        if ($result) {
            $keys = array();
            foreach ($result as $array) {
                $keys[] = $array['hour'];
            }
            $result = array_combine($keys, $result);
            for ($i=0; $i < 24; $i++) {
                if (!isset($result[$i])) {
                    $result[$i] = array(
                        'hour' => $i,
                        'OK' => 0,
                        'WARNING' => 0,
                        'CRITICAL' => 0,
                        'UNKNOWN' => 0,
                    );
                }
            }
            ksort($result);
        }

        return $result;

    }

    public function importFromNagios($data) {
        return false;
    }

    protected function _install() {
    }

}