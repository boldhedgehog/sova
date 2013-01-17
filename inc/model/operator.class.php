<?php

/**
 * This file contain operatorModel class.
 * This class contain method for operating any user related data in DB.
 *
 *
 * @copyright  2010
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */
class operatorModel extends nagiosObjectModel
{

    protected $tableName = "operator";
    protected $primaryKey = "operator_id";

    protected $_allowedSettings = array(
        'display_host'
    );

    protected function _loadNagiosData()
    {
        $this->data["nagios"] = $this->livestatusModel->getContactByName($this->data["nagios_name"]);
        return $this;
    }

    public function saveSettings($id, $data)
    {
        $id = (int) $id;

        if (!$id) {
            throw new Exception('Operator with this is does not exist');
        }

        $this->_allowedSettings = array_combine($this->_allowedSettings, $this->_allowedSettings);

        // make sure we save only allowed settings
        $data = array_intersect_key($data, $this->_allowedSettings);

        if (is_array($data)) {
            $data = serialize($data);
        }

        $id = $this->getColumn($id, $this->primaryKey);

        if (!$id) {
            throw new Exception('Operator with this is does not exist');
        }

        $stmt = $this->db->prepare(
            "UPDATE `{$this->tableName}` AS `{$this->alias}`
                SET `settings` = :settings
                WHERE `{$this->alias}`.`{$this->primaryKey}`=:key"
        );

        $stmt->execute(
            array(
                ':key' => $id,
                ':settings' => $data
            )
        );

        $data = $this->getColumn($id, 'settings');

        return $data ? unserialize($data) : null;
    }

    public function importFromNagios($data)
    {
        $this->_install();

        echo "<pre>" . print_r($data, true) . "</pre>";

        try {
            foreach ($data as $value) {
                $this->resetFilters();
                $operatorInfo = $this->getByNagiosName($value["name"]);

                if (is_array($operatorInfo) && count($operatorInfo) > 0) {
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

    protected function _install()
    {
        $stmt = $this->db->prepare(
            'CREATE TABLE IF NOT EXISTS `operator` (
              `operator_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `nagios_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `email` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
              `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`operator_id`),
              KEY `name` (`name`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1'
        );

        $stmt->execute();
    }

}