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
class companyModel extends basicModel {

    protected $tableName = "company";
    protected $primaryKey = "company_id";

    public function getByHostId($id) {
        try {
            $rows = $this->db->fetchAll("SELECT `{$this->alias}`.* FROM `{$this->tableName}` AS `{$this->alias}`
INNER JOIN `company_to_host` AS `ch` USING(`company_id`)
WHERE `ch`.`host_id`= :host_id
ORDER BY `ch`.`rank` ASC", array(":host_id" => $id), true);
        } catch (PDOException $e) {
            return $e;
        }
        return $rows;
    }
}