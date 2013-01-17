<?php

/**
 * Telephone
 *
 *
 * @copyright  2012
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */

class telephoneId extends keyDatatype {
    protected $name = "telephone_id";
}

class telephoneNumber extends stringDatatype {
    protected $name = "number";
}

class telephoneNotes extends stringDatatype {
    protected $name = "notes";
}

class telephoneModel extends basicModel {

    protected $tableName = "telephone";
    protected $primaryKey = "telephone_id";

    public function __construct($dbms = NULL) {

        $this->addField(new telephoneId($this))
            ->addField(new telephoneNumber($this))
            ->addField(new telephoneNotes($this))
        ;

        return parent::__construct($dbms);
    }
}