<?php

/**
 * This file contain alertCardModel class.
 * This class contain method for operating any user related data in DB.
 *
 *
 * @copyright  2010
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */

class alertCardId extends keyDatatype {
    protected $name = "alert_card_id";
}

class alertCardServiceId extends keyDatatype {
    protected $name = "service_id";
}

class alertCardName extends stringDatatype {
    protected $name = "name";
}

class alertCardEmergencyType extends stringDatatype {
    protected $name = "emergency_type";
}

class alertCardEmergencyCode extends stringDatatype {
    protected $name = "emergency_code";
}

class alertCardEmergencyScenario extends textDatatype {
    protected $name = "emergency_scenario";
}

class alertCardAffectArea extends stringDatatype {
    protected $name = "affect_area";
}

class alertCardEmergencyLevel extends stringDatatype {
    protected $name = "emergency_level";
}

class alertCardEmergencyLevel2 extends stringDatatype {
    protected $name = "emergency_level2";
}

class alertCardDetectorId extends stringDatatype {
    protected $name = "detector_id";
}

class alertCardDetectorCategoryId extends integerDatatype {
    protected $name = "detector_category_id";
}

class alertCardControlledAgent extends stringDatatype {
    protected $name = "controlled_agent";
}

class alertCardAgentQuantityUnit extends stringDatatype {
    protected $name = "agent_quantity_unit";
}

class alertCardControlledAgentQuantity extends stringDatatype {
    protected $name = "controlled_agent_quantity";
}

class alertCardDescription extends textDatatype {
    protected $name = "description";
}

/**
 *
 * @author ISM-UKRAINE\a.yegorov
 *
 */
class alertCardModel extends basicModel {

    protected $tableName = "alert_card";
    protected $primaryKey = "alert_card_id";

    public function __construct($dbms = NULL) {

        $this->addField(new alertCardId($this));
	$this->addField(new alertCardServiceId($this));
        $this->addField(new alertCardName($this));
	$this->addField(new alertCardEmergencyType($this));
	$this->addField(new alertCardEmergencyCode($this));
	$this->addField(new alertCardEmergencyScenario($this));
	$this->addField(new alertCardAffectArea($this));
	$this->addField(new alertCardEmergencyLevel($this));
	$this->addField(new alertCardEmergencyLevel2($this));
	$this->addField(new alertCardDetectorId($this));
	$this->addField(new alertCardDetectorCategoryId($this));
	$this->addField(new alertCardControlledAgent($this));
	$this->addField(new alertCardAgentQuantityUnit($this));
        $this->addField(new alertCardControlledAgentQuantity($this));
	$this->addField(new alertCardDescription($this));

        return parent::__construct($dbms);
    }

    public function getByServiceId($service_id) {
	//$this->resetFilters()->resetJoins()->addLeftJoin("zoneModel", array("zone_id"=>"zone_id"), "zone");

	return $this->setFilter("service_id", "=", $service_id)->getAll();
	/*return $this->db->fetchAll("SELECT * FROM `alert_card` AS `ac`
				WHERE `ac`.`service_id`= :service_id",
				array(":service_id" => $service_id), true);*/
    }

    private function _install() {
	$stmt = $this->db->prepare("CREATE TABLE IF NOT EXISTS `alert_card` (
  `alert_card_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Найменування технологічного процесу',
  `emergency_type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Вид небезпеки згідно Методики ідентифікації ПНО додаток 3',
  `emergency_code` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Код можливих НС згідно Методики ідентифікації ПНО додаток 1',
  `emergency_scenario` text COLLATE utf8_unicode_ci COMMENT 'Перелік сценаріїв можливих аварій',
  `sensor_number` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '№ ДАТЧИКА, що контролює процес',
  `affect_area` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Зона можливого ураження(радіус, метрів,граничні ознаки ураження)',
  `emergency_level` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Рівень можливих НС згідно Методики ідентифікації ПНО додаток 4',
  `emergency_level2` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Рівень можливих НС згідно НПАОП 0.00-4.33-99',
  `detector_id` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Унікальний код датчика в даній зоні контролю',
  `detector_category_id` tinyint(4) DEFAULT NULL COMMENT 'Категорія датчика, відповідно до з наказом № 288, п. 7.5.1.2 (див. «Датчики контролю»), тобто від 1 до 6',
  `controlled_agent` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Назва контрольованої речовини',
  `agent_quantity_unit` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Одиниця вимірювання контрольованої речовини (відповідно до «Класифікатором системі позначень одиниць вімірювання та обліку ДК 011-96» від 01.07.97 р',
  `controlled_agent_quantity` decimal(10,0) DEFAULT NULL COMMENT 'Кількість контрольованої речовини (для трубопроводів - пропускна спроможність) (у форматі x.x)',
  `description` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Описание',
  PRIMARY KEY (`alert_card_id`),
  KEY `service_id` (`service_id`),
  KEY `zone_id` (`zone_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Датчики и потенциальные аварии' AUTO_INCREMENT=1
");

	$stmt->execute();
    }

}