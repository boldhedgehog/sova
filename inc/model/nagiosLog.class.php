<?php

/**
 * This file contain nagiosLogModel class.
 * This class contain method for operating any user related data in DB.
 *
 *
 * @copyright  2010
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */
class nagioslogId extends keyDatatype
{
    protected $name = "nagioslog_id";
}

class nagioslogHostName extends stringDatatype
{
    protected $name = "host_name";
}

class nagioslogServiceDescription extends stringDatatype
{
    protected $name = "service_description";
}

class nagioslogServiceNotes extends stringDatatype
{
    protected $name = "service_notes";
}

class nagioslogTime extends timestampDatatype
{
    protected $name = "time";
}

class nagioslogState extends integerDatatype
{
    protected $name = "state";
}

class nagioslogServiceId extends keyDatatype
{
    protected $name = "service_id";
}

class nagioslogHostId extends keyDatatype
{
    protected $name = "host_id";
}

class nagioslogPluginOutput extends stringDatatype
{
    protected $name = "plugin_output";
}

class nagioslogMessage extends textDatatype
{
    protected $name = "message";
}

/**
 *
 */
class nagioslogModel extends nagiosObjectModel
{

    protected $tableName = 'nagioslog';
    protected $primaryKey = 'nagioslog_id';

    public function __construct($dbms = NULL)
    {

        $this->addField(new nagioslogId($this))
                ->addField(new nagioslogTime($this))
                ->addField(new nagioslogState($this))
                ->addField(new nagioslogHostName($this))
                ->addField(new nagioslogServiceDescription($this))
                ->addField(new nagioslogServiceNotes($this))
                ->addField(new nagioslogHostId($this))
                ->addField(new nagioslogServiceId($this))
                ->addField(new nagioslogPluginOutput($this))
                ->addField(new nagioslogMessage($this));

        return parent::__construct($dbms);
    }

    public function getLog() {
        $data = $this->setOrder('time', basicModel::ORDER_DESC)->getAll();

        array_walk($data, function (& $data) {
            if (isset($data['state'])) {
                $data['state_text'] = nagioslogModel::stateCaption($data['state']);
            }
        });

        return $data;
    }

    public function getLogDailyData() {
        list($query, $bindings) = $this->_formatGetAllQuery();
        $query = preg_replace(
            '/ORDER BY .*$/',
            'GROUP BY HOUR(FROM_UNIXTIME(`time`))
ORDER BY HOUR(FROM_UNIXTIME(`time`))  ASC',
            $query
        );
        $query = preg_replace(
            '/SELECT (.*?) FROM/',
            'SELECT HOUR(FROM_UNIXTIME(`time`)) AS `hour`,
COUNT(IF(`state` = 0, 1, NULL)) AS `OK`,
COUNT(IF(`state` = 1, 1, NULL)) AS `WARNING`,
COUNT(IF(`state` = 2, 1, NULL)) AS `CRITICAL`,
COUNT(IF(`state` = 3, 1, NULL)) AS `UNKNOWN`
FROM',
            $query
        );

        if ($result = $this->db->fetchAll($query, $bindings, true)) {
            $keys = array();
            foreach ($result as $array) {
                $keys[] = $array['hour'];
            }
            $result = array_combine($keys, $result);
            for ($i=0; $i < 24; $i++) {
                if (!isset($result[$i])) {
                    $result[$i] = array(
                        'hour' => $i,
                        'OK' => 0,
                        'WARNING' => 0,
                        'CRITICAL' => 0,
                        'UNKNOWN' => 0,
                    );
                }
            }
            ksort($result);
        }

        return $result;
    }

    protected function _loadNagiosData()
    {
        $this->data["nagios"] = array();
        return $this;
    }

    public function importLog()
    {
        return $this;
    }

    public function importFromNagios($data)
    {
        return false;
    }


    protected function _install()
    {
    }

}