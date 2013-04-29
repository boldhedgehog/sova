<?php

/**
 * This file contain hostRegistryModel class.
 * This class contain method for operating any user related data in DB.
 *
 *
 * @copyright  2013
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */

class hostRegistryId extends keyDatatype {
    protected $name = "host_registry_id";
}

class hostRegistryHostId extends keyDatatype {
    protected $name = "host_id";
}

class hostRegistryDatetime extends stringDatatype
{
    protected $name = "datetime";
    protected $caption = "Дата/Час";
}

class hostRegistryPublished extends booleanDatatype
{
    protected $name = "published";
    protected $caption = "Активне";
}


class hostRegistryPerson extends stringDatatype {
    protected $name = "person";
    protected $caption = "Виконавець";
}

class hostRegistryDescription extends stringDatatype {
    protected $name = "description";
    protected $caption = "Текст";
}

/**
 *
 * @author a.yegorov
 *
 */
class hostRegistryModel extends basicModel {

    protected $tableName = "host_registry";
    protected $primaryKey = "host_registry_id";

    public function __construct($dbms = NULL) {

        $this->addField(new hostRegistryId($this))
                ->addField(new hostRegistryHostId($this))
                ->addField(new hostRegistryPerson($this))
                ->addField(new hostRegistryDescription($this))
                ->addField(new hostRegistryDatetime($this))
                ->addField(new hostRegistryPublished($this))
                ;

        return parent::__construct($dbms);
    }
    
    public function getByHostId($id) {
        return $this->resetFilters()
            ->setFilter('host_id', '=', $id)
            ->setFilter('published', '=', true)
            ->setOrder('datetime', self::ORDER_DESC)
            ->getAll()
            ;

    }

}