<?php

/**
 * This file contain channelModel class.
 * This class contain method for operating any user related data in DB.
 *
 *
 * @copyright  2011
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */

class channelId extends keyDatatype {
    protected $name = "channel_id";
}

class channelHostId extends keyDatatype {
    protected $name = "host_id";
}

class channelName extends stringDatatype {
    protected $name = "name";
    protected $caption = "Найменування";
}

class channelType extends choiceDatatype {
    protected $name = "type";
    protected $caption = "Вид";
    protected $choices = array('cell'=>'Сотовий','xdsl'=>'xDSL', 'ethernet'=>'Ethernet', 'other'=>'Інше');
}
    
class channelIp extends stringDatatype {
    protected $name = "ip";
    protected $caption = "IP адреса";
    protected $validationRegexp = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/';
}

class channelCellNumberId extends stringDatatype {
    protected $name = "cell_number_id";
    protected $caption = "Номер сотового";
}

class channelNotes extends stringDatatype {
    protected $name = "notes";
    protected $caption = "Примітки";
}

/**
 *
 * @author ISM-UKRAINE\a.yegorov
 *
 */
class channelModel extends basicModel {

    protected $tableName = "channel";
    protected $primaryKey = "channel_id";

    public function __construct($dbms = NULL) {

        $this->addField(new channelId($this))
                ->addField(new channelHostId($this))
                ->addField(new channelName($this))
                ->addField(new channelIp($this))
                ->addField(new channelType($this))
                ->addField(new channelCellNumberId($this))
                ->addField(new channelNotes($this))
                ;

        $this->resetJoins()
            ->addLeftJoin("telephoneModel", array("cell_number_id"=>"telephone_id"), "cell_number");

        return parent::__construct($dbms);
    }
    
    public function getByHostId($id) {
/*        try {
            $rows = $this->db->fetchAll("SELECT `{$this->alias}`.* FROM `{$this->tableName}` AS `{$this->alias}`
WHERE `{$this->alias}`.`host_id`= :host_id
ORDER BY `{$this->alias}`.`name` ASC", array(":host_id" => $id), true);
        } catch (PDOException $e) {
            return $e;
        }

        return $rows;
*/
        return $this->resetFilters()
            ->setFilter('host_id', '=', $id)
            ->getAll()
            ;

    }

}