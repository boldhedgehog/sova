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

class sensorTypeId extends keyDatatype {
    protected $name = "sensor_type_id";
}

class sensorTypeName extends stringDatatype {
    protected $name = "name";
}

/**
 *
 * @author ISM-UKRAINE\a.yegorov
 *
 */
class sensorTypeModel extends basicModel {

    protected $tableName = "sensor_type";
    protected $primaryKey = "sensor_type_id";

    public function __construct($dbms = NULL) {

        $this->addField(new sensorTypeId($this))
	    ->addField(new sensorTypeName($this))
	    ;

        return parent::__construct($dbms);
    }
    
}