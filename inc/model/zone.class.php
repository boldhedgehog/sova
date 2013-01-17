<?php

/**
 * This file contain zoneModel class.
 * This class contain method for operating any user related data in DB.
 *
 *
 * @copyright  2010
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */

class zoneId extends keyDatatype {
    protected $name = "zone_id";
}

class zoneHostId extends keyDatatype {
    protected $name = "host_id";
}

class zonePlasZoneId extends stringDatatype {
    protected $name = "plas_zone_id";
}

class zoneName extends stringDatatype {
    protected $name = "name";
}

class zoneType extends stringDatatype {
    protected $name = "type";
    protected $caption = "Тип";
}

class zoneNumStoreys extends integerDatatype {
    protected $name = "num_storeys";
}

/*
class zoneFloor extends integerDatatype {
    protected $name = "floor";
}
*/

/*
class zoneTopology extends stringDatatype {
    protected $name = "topology_name";
}

class zoneRoad extends stringDatatype {
    protected $name = "road_name";
}
*/

class zoneEntrancePositionSystem extends stringDatatype {
    protected $name = "entrance_position_system";
    protected $caption = "Система вимірювання географічних координат";
    protected $defaultValue = "WSG-84";
}

class zoneEntrancePositionLongitude extends decimalDatatype {
    protected $name = "entrance_position_longitude";
    protected $caption = "Довгота (у форматі xx.xxxx)";
}

class zoneEntrancePositionLatitude extends decimalDatatype {
    protected $name = "entrance_position_latitude";
    protected $caption = "Широта (у форматі xx.xxxx)";
}

/*
class zoneHardwareType extends stringDatatype {
    protected $name = "hardware_type";
    protected $caption = "Тип обладнання";
}
*/

class zoneSchemeImage extends imageDatatype {
    protected $name = "scheme_image_name";
    protected $caption = "Схема";
}

/**
 *
 * @author ISM-UKRAINE\a.yegorov
 *
 */
class zoneModel extends basicModel {

    protected $tableName = "zone";
    protected $primaryKey = "zone_id";

    public function __construct($dbms = NULL) {

        $this->addField(new zoneId($this))
	    ->addField(new zoneHostId($this))
            ->addField(new zonePlasZoneId($this))
            ->addField(new zoneType($this))
	    ->addField(new zoneName($this))
	    ->addField(new zoneNumStoreys($this))
	    //->addField(new zoneFloor($this))
	    //->addField(new zoneTopology($this))
	    //->addField(new zoneRoad($this))
	    ->addField(new zoneEntrancePositionSystem($this))
	    ->addField(new zoneEntrancePositionLongitude($this))
	    ->addField(new zoneEntrancePositionLatitude($this))
	    //->addField(new zoneHardwareType($this))
        ->addField(new zoneSchemeImage($this))
	    ;

        return parent::__construct($dbms);
    }
    
    public function getByHostId($id) {
        try {
            $rows = $this->db->fetchAll("SELECT `{$this->alias}`.* FROM `{$this->tableName}` AS `{$this->alias}`
WHERE {$this->alias}.`host_id`= :host_id
ORDER BY {$this->alias}.`plas_zone_id` ASC", array(":host_id" => $id), true);
        } catch (PDOException $e) {
            return $e;
        }
        return $rows;
    }

    private function _install() {
	$stmt = $this->db->prepare("CREATE TABLE IF NOT EXISTS `zone` (
  `zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL,
  `plas_zone_id` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zone_num_storeys` int(11) DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `topology` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `road` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`zone_id`),
  UNIQUE KEY `host_zone` (`host_id`,`plas_zone_id`),
  KEY `plas_zone_id` (`plas_zone_id`),
  KEY `host_id` (`host_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1
");

	$stmt->execute();
    }

}