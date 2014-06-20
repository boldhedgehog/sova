<?php

/**
 * This file contain notificationDeviceModel class.
 * This class contain method for operating any user related data in DB.
 *
 *
 * @copyright  2012
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */

class notificationDeviceId extends keyDatatype {
    protected $name = "notification_device_id";
}

class notificationDeviceHostId extends keyDatatype {
    protected $name = "host_id";
}

class notificationDeviceZoneId extends keyDatatype {
    protected $name = "zone_id";
}

class notificationDeviceName extends stringDatatype {
    protected $name = "name";
}

class notificationDeviceDeviceId extends keyDatatype
{
    protected $name = "device_id";
}

class notificationDeviceSchemeImage extends imageDatatype
{
    protected $name = "scheme_image_name";
    protected $caption = "Схема";
}

/**
 *
 * @author ISM-UKRAINE\a.yegorov
 *
 */
class notificationDeviceModel extends basicModel {

    protected $tableName = "notification_device";
    protected $primaryKey = "notification_device_id";

    public function __construct($dbms = NULL) {

        $this->addField(new notificationDeviceId($this))
            ->addField(new notificationDeviceHostId($this))
            ->addField(new notificationDeviceZoneId($this))
            ->addField(new notificationDeviceName($this))
            ->addField(new notificationDeviceDeviceId($this))
            ->addField(new notificationDeviceSchemeImage($this))
            ;

        $this->resetJoins()
            ->addLeftJoin("zoneModel", array("zone_id"=>"zone_id"), "zone")
            ->addLeftJoin("communicationDeviceModel", array("device_id"=> "communication_device_id"), "communication_device")
            ;

        return parent::__construct($dbms);
    }

    public function getByHostId($id) {
        try {
            $rows = $this->db->fetchAll("SELECT `{$this->alias}`.* FROM `{$this->tableName}` AS `{$this->alias}`
WHERE {$this->alias}.`host_id`= :host_id
ORDER BY {$this->alias}.`zone_id` ASC, {$this->alias}.`device_id` ASC, {$this->alias}.`name` ASC", array(":host_id" => $id), true);
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