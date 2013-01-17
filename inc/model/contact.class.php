<?php

/**
 * This class contain method for operating any user related data in DB.
 *
 *
 * @copyright  2010
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */
class contactModel extends nagiosObjectModel {

    protected $tableName = "contact";
    protected $primaryKey = "contact_id";

    public function getByHostId($id) {
        try {
            $rows = $this->db->fetchAll("SELECT `{$this->alias}`.* FROM `{$this->tableName}` AS `{$this->alias}`
INNER JOIN `contact_to_host` AS `ch` USING(`contact_id`)
WHERE `ch`.`host_id`= :host_id
ORDER BY `ch`.`rank` ASC", array(":host_id" => $id), true);
        } catch (PDOException $e) {
            return $e;
        }
        return $rows;
    }

    protected function _loadNagiosData() {
        $this->data["nagios"] = $this->livestatusModel->getContactByName($this->data["nagios_name"]);
        return $this;
    }

    public function importFromNagios($data) {
        $this->_install();

        echo "<pre>" . print_r($data, true) . "</pre>";

        try {
            foreach ($data as $value) {
                $this->resetFilters();
                $data = $this->getByNagiosName($value["name"]);

                if (is_array($data) && count($data) > 0) {
                    continue;
                }

                $stmt = $this->db->prepare('
					INSERT INTO `operator` (`nagios_name`, `name`, `email`, `created`)
					VALUES (:nagios_name, :name, :email, NOW())
				');
                $stmt->execute(array(":nagios_name" => $value["name"], ":name" => $value["alias"], ":email" => $value["email"]));
            }
        } catch (PDOException $e) {
            return array('status' => false, 'message' => $e->getMessage());
        }

        return array('status' => true, 'message' => '');
    }

    protected function _install() {
        $stmt = $this->db->prepare('CREATE TABLE IF NOT EXISTS `operator` (
									  `operator_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
									  `nagios_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
									  `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
									  `email` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
									  `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
									  PRIMARY KEY (`operator_id`),
									  INDEX `name`(`name`)
									)
									ENGINE = MyISAM
									AUTO_INCREMENT = 1
		');

        $stmt->execute();
    }

}