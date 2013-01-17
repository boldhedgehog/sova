<?php
/**
 * This file contain alertModel class.
 * This class contain method for operating alerts related data in DB.
 *
 *
 * @copyright  2010
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */
class alertId extends keyDatatype {
    protected $name = "alert_id";
}

class alertTimestamp extends timestampDatatype {
    protected $name = "timestamp";
    protected $caption = "Дата/время";
}

class alertMessage extends stringDatatype {
    protected $name = "message";
    protected $caption = "Сообщение";
}

class alertNotes extends stringDatatype {
    protected $name = "notes";
    protected $caption = "Примечания";
}

class alertOperator extends keyDatatype {
    protected $name = "operator_id";
    protected $caption = "Оператор";
}

class alertHost extends stringDatatype {
    protected $name = "host";
    protected $caption = "Узел";
}

class alertService extends stringDatatype {
    protected $name = "service";
    protected $caption = "Сервис";
}

class alertServiceId extends keyDatatype {
    protected $name = "service_id";
    protected $caption = "Сервис БД";
}

class alertStatus extends integerDatatype {
    protected $name = "status";
    protected $caption = "Состояние";
}

class alertType extends integerDatatype {
    protected $name = "type";
    protected $caption = "Тип";
}

class alertServicesData extends serializedDatatype {
    protected $name = "services_data";
    protected $caption = "Состояние сервисов";
}

/**
 *
 * @author Alexander Yegorov
 *
 */
class alertModel extends basicModel {

    protected $tableName = "alert";
    protected $primaryKey = "alert_id";

    const STATUS_NEW		= 0;
    const STATUS_PROCESSING	= 1;
    const STATUS_ACKNOWLEDGED	= 2;
    const STATUS_CANCELED	= 3;
    const STATUS_ONHOLD		= 4;

    const TYPE_TEST = 0;
    const TYPE_DRILL = 1;
    const TYPE_WAR = 2;

    protected $indexes = array("operator_id", "host", "service", "service_id", "timestamp", "state", array("host", "service"), array("host", "service", "timestamp"));

    public function __construct($dbms = NULL) {

	$this->addField(new alertId($this));
	$this->addField(new alertTimestamp($this));
	$this->addField(new alertOperator($this));
	$this->addField(new alertHost($this));
	$this->addField(new alertService($this));
	$this->addField(new alertServiceId($this));
	$this->addField(new alertMessage($this));
	$this->addField(new alertNotes($this));
	$this->addField(new alertStatus($this));
	$this->addField(new alertType($this));
	$this->addField(new alertServicesData($this));

	return parent::__construct($dbms);
    }

    /**
     * Add new alert
     */
    public function createAlert($data) {
	try {
	    // get service_id from the DB
	    $model = new serviceModel();
	    $service = $model->loadByNagiosName($data[":service"])->getData();
	    $data["service_id"] = $service["service_id"];
	    $stmt = $this->db->prepare("
				INSERT INTO `{$this->tableName}` (`timestamp`, `state`, `host`, `service`,`service_id`, `message`, `services_data`)
				VALUES (:timestamp, :state, :host, :service, :service_id, :message, :services_data)
				ON DUPLICATE KEY UPDATE `timestamp`=`timestamp`;
			");
	    $stmt->execute($data);
	    return $this->lastInsertId = $this->db->lastInsertId();
	} catch (PDOException $e) {
	    return $e;
	}
    }

    public function processAlert($data) {
	switch ($data['status']) {
	    case "acknowledged":
	    case self::STATUS_ACKNOWLEDGED:
		$data[":status"] = self::STATUS_ACKNOWLEDGED;
		break;

	    case "canceled":
	    case self::STATUS_CANCELED:
		$data[":status"] = self::STATUS_CANCELED;
		break;

	    case "onhold":
	    case self::STATUS_ONHOLD:
		$data[":status"] = self::STATUS_ONHOLD;
		break;

	    default:
		$data[":status"] = self::STATUS_ACKNOWLEDGED;
		break;
	}

	try {
	    $stmt = $this->db->prepare("
				UPDATE `{$this->tableName}` SET `notes` = :notes, `type` = :type, `status` = :status
				WHERE `{$this->primaryKey}` = :alert_id AND `status` != :status;
			");
	    $stmt->execute($data);
	} catch (PDOException $e) {
	    return $e;
	}
	return $this;
    }

    /**
     * Выбрать тревогу или новую, или необработанную текущим оператором
     * @param $operator_id
     */
    public function getNewAlert($operator_id) {
	try {
	    $rows = $this->db->fetchOne(
			    "SELECT * FROM `{$this->tableName}` AS `{$this->alias}`
				WHERE (`{$this->alias}`.`status`= 0 AND `{$this->alias}`.`operator_id` IS NULL)
				OR (`{$this->alias}`.`status`= 1 AND `{$this->alias}`.`operator_id` = :operator_id)
				ORDER BY `timestamp` ASC
				LIMIT 1", array(":operator_id" => $operator_id), true);
	} catch (PDOException $e) {
	    return $e;
	}
	return $rows;
    }

    public function getAlert($id) {
	try {
	    $rows = $this->db->fetchAll(
			    "SELECT * FROM `{$this->tableName}` AS `{$this->alias}`
				WHERE `{$this->alias}`.`status`= 0 AND `{$this->alias}`.`operator_id` IS NULL
				ORDER BY `timestamp` ASC LIMIT 1", array(), true);
	} catch (PDOException $e) {
	    return $e;
	}
	return $rows;
    }

    public function getAlertFull($id) {
	try {
	    $alert = $this->db->fetchAll(
			    "SELECT * FROM `{$this->tableName}` AS `{$this->alias}`
				WHERE `{$this->alias}`.`{$this->primaryKey}`= :{$this->primaryKey}",
			    array(":{$this->primaryKey}" => $id), true);

	    if (!is_array($alert) || !count($alert))
		return array();

	    $alert = $alert[0];

	    $model = new hostModel();
	    $alert["host"] = $model->loadByNagiosName($alert["host"])->loadContacts()->loadNagiosData()->getData();
	    if ($alert["host"] instanceof PDOException)
		throw $alert["host"];

	    $model = new serviceModel();
	    $alert["service"] = $model->loadByHostIdAndNagiosName($alert["host"]["host_id"], $alert["service"])->getData();
	    if ($alert["service"] instanceof PDOException)
		throw $alert["service"];

	    if ($alert["operator_id"]) {
		$model = new operatorModel();
		$alert["operator"] = $model->getOne($alert["operator_id"]);
		if ($alert["operator"] instanceof PDOException)
		    throw $alert["operator"];
	    }

	    $alert["cards"] = $this->db->fetchAll(
			    "SELECT * FROM `alert_card` AS `ac`
				WHERE `ac`.`service_id`= :service_id",
			    array(":service_id" => $alert["service_id"]), true);
	} catch (PDOException $e) {
	    return $e;
	}
	return $alert;
    }

    public function setStatus($id, $status) {
	try {
	    $stmt = $this->db->prepare("
				UPDATE `{$this->tableName}` SET `status` = :status
				WHERE `{$this->primaryKey}` = :{$this->primaryKey};
			");
	    $stmt->execute(array(":{$this->primaryKey}" => $id, ":status" => $status));
	} catch (PDOException $e) {
	    return $e;
	}
	return $this;
    }

    public function startProcessing($id, $operator_id, $timestamp) {
	try {
	    $this->assertNotEmpty($id, $operator_id, $timestamp);

	    // Нужно выполнить эту операцию в качестве атомарной
	    $onlineOperatorModel = new onlineOperatorModel();
	    $stmt = $this->db->prepare("
				LOCK TABLES `{$this->tableName}` WRITE, `" . $onlineOperatorModel->getTableName() . "` WRITE;
				UPDATE `{$this->tableName}` SET `status` = :status, `operator_id` = :operator_id
				WHERE `{$this->primaryKey}` = :{$this->primaryKey} AND `status` != :processedstatus  AND `status` != :canceledstatus;
				UPDATE `" . $onlineOperatorModel->getTableName() . "` SET `lastvisit`=:timestamp, `status`=:opstatus WHERE `operator_id`=:operator_id;
				UNLOCK TABLES;
			");
	    $stmt->execute(array(":{$this->primaryKey}" => $id, ":status" => self::STATUS_PROCESSING
		, ":processedstatus" => self::STATUS_ACKNOWLEDGED
		, ":canceledstatus" => self::STATUS_CANCELED
		, ":operator_id" => $operator_id, ":timestamp" => $timestamp
		, ":opstatus" => onlineOperatorModel::OPERATOR_STATUS_BUSY));
	} catch (PDOException $e) {
	    return $e;
	}
	return $this;
    }

    public function getLastAlert() {
	try {
	    $rows = $this->db->fetchOne(
			    "SELECT * FROM `{$this->tableName}` AS `{$this->alias}`
				ORDER BY `timestamp` ASC
				LIMIT 1", array(), true);
	} catch (PDOException $e) {
	    return $e;
	}
	return $rows;
    }

    public function getLastAlertTimestamp() {
	try {
	    $rows = $this->db->fetchOne(
			    "SELECT `timestamp` FROM `{$this->tableName}` AS `{$this->alias}`
				ORDER BY `timestamp` ASC
				LIMIT 1", array(), true);
	} catch (PDOException $e) {
	    return $e;
	}
	return (isset($rows["timestamp"]))?$rows["timestamp"]:NULL;
    }

    private function _install() {
	$stmt = $this->db->prepare("CREATE TABLE IF NOT EXISTS `alert` (
  `alert_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` bigint(20) unsigned NOT NULL,
  `state` tinyint(1) NOT NULL COMMENT 'Состояние сервиса в Nagios',
  `message` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `notes` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  `host` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_id` int(11) NOT NULL,
  `operator_id` bigint(20) DEFAULT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `type` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`alert_id`),
  KEY `operator_id` (`operator_id`),
  KEY `host_service` (`host`,`service`),
  KEY `timestamp` (`timestamp`),
  KEY `serivce_id` (`service_id`),
  KEY `state` (`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1
");

	$stmt->execute();
    }

}

?>