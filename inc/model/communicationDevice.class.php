<?php

/**
 * This file contain communicationDeviceModel class.
 * This class contain method for operating any user related data in DB.
 *
 *
 * @copyright  2010
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */

class communicationDeviceId extends keyDatatype {
    protected $name = "communication_device_id";
}

class communicationDeviceHostId extends keyDatatype {
    protected $name = "host_id";
}

class communicationDeviceZoneId extends keyDatatype {
    protected $name = "zone_id";
}

class communicationDeviceName extends stringDatatype {
    protected $name = "name";
}

class communicationDeviceSerialNumber extends stringDatatype {
    protected $name = "serial_number";
}

class communicationDeviceNotes extends stringDatatype {
    protected $name = "notes";
}

class communicationDeviceLogicalNumber extends integerDatatype {
    protected $name = "logical_number";
}


/**
 *
 * @author ISM-UKRAINE\a.yegorov
 *
 */
class communicationDeviceModel extends basicModel {

    protected $tableName = "communication_device";
    protected $primaryKey = "communication_device_id";

    public function __construct($dbms = NULL) {

        $this->addField(new communicationDeviceId($this))
            ->addField(new communicationDeviceHostId($this))
            ->addField(new communicationDeviceZoneId($this))
            ->addField(new communicationDeviceName($this))
            ->addField(new communicationDeviceSerialNumber($this))
            ->addField(new communicationDeviceNotes($this))
            ->addField(new communicationDeviceLogicalNumber($this))
            ;

        $this->resetJoins()
            ->addLeftJoin("zoneModel", array("zone_id"=>"zone_id"), "zone")
            ;

        return parent::__construct($dbms);
    }

    public function getByHostId($id) {
        try {
            $rows = $this->db->fetchAll("SELECT `{$this->alias}`.* FROM `{$this->tableName}` AS `{$this->alias}`
WHERE {$this->alias}.`host_id`= :host_id
ORDER BY {$this->alias}.`name` ASC", array(":host_id" => $id), true);
        } catch (PDOException $e) {
            return $e;
        }
        return $rows;
    }

    public function getByHost(hostModel $host) {
        if (!$host->getId()) {
            return NULL;
        }

        $hostData = $host->getData();
        $data = $this->resetFilters()
            ->resetOrders()
            ->setOrder('name', self::ORDER_ASC)
            ->setFilter('host_id', '=', $host->getId())
            ->getAll();

        $keys = array();
        foreach ($data as $value) {
            $keys[] = $value[$this->primaryKey];
        }

        return (is_array($data) && $data && $keys) ? array_combine($keys, $data) : array();
    }

    private function _install() {
    }

}