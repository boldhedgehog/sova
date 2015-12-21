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
abstract class nagiosObjectModel extends basicModel
{
    const STATE_OK = 0;
    const STATE_WARNING = 1;
    const STATE_CRITICAL = 2;
    const STATE_UNKNOWN = 3;

    // special state for PENDING
    const STATE_PENDING = -1;

    protected static $stateCaptions = array(
        self::STATE_OK => 'OK',
        self::STATE_WARNING => 'WARNING',
        self::STATE_CRITICAL => 'CRITICAL',
        self::STATE_UNKNOWN => 'UNKNOWN',
        self::STATE_PENDING => 'PENDING'
    );

    const NAGVIS_REGEX_PART_TYPE = 1;
    const NAGVIS_REGEX_PART_DEFINITION = 2;

    static function stateCaption($state)
    {
        return self::$stateCaptions[intval($state)];
    }

    /* @var mklivestatusModel */
    protected $mklivestatusModel = false;

    public function __construct($dbms = NULL)
    {
        $this->livestatusModel = new mklivestatusModel();

        return parent::__construct($dbms);
    }

    public function  load($key)
    {
        parent::load($key);
        $this->data['has_geo'] = false;
        return $this->_addNagvisUrls();
    }

    /**
     *
     * @param string $name
     */
    public function loadByName($name)
    {

        if ($name == '')
            return $this;

        $this->resetFilters();
        $this->setFilter("name", "=", $name);

        $data = $this->getAll();

        if (is_array($data) && count($data) > 0) {
            $this->data = array_pop($data);
            $this->_addNagvisUrls();
        } else {
            $this->data = false;
        }

        return $this;
    }

    /**
     *
     * @param string $name
     */
    public function loadByNagiosName($name)
    {
        if ($name == '')
            return false;
        $this->resetFilters();
        $this->setFilter("nagios_name", "=", $name);

        $data = $this->getAll();

        if (is_array($data) && count($data) > 0) {
            $this->data = array_pop($data);
            $this->_addNagvisUrls();
        } else {
            $this->data = false;
        }

        return $this;
    }

    public function loadNagiosData($key = NULL)
    {
        if (!$this->data || !isset($this->data[$this->primaryKey])) {
            if (is_null($key)) {
                return $this;
            }
            $this->load($key);
        }

        $this->_loadNagiosData();

        return $this;
    }

    protected function _addNagvisUrls()
    {
        if (isset($this->data["nagvis_map_name"]) && $this->data["nagvis_map_name"]) {
            $this->data["nagvis_map_url"] = sprintf(NAGVIS_MAP_URL, $this->data["nagvis_map_name"]);
            $this->_addNagvisMapConfig();
        }
        if (isset($this->data["nagvis_thumb_name"]) && $this->data["nagvis_thumb_name"]) {
            $this->data["nagvis_thumb_url"] = NAGVIS_IMAGES_URL . $this->data["nagvis_thumb_name"];
        }

        return $this;
    }

    protected function _addNagvisMapConfig()
    {
        if (!file_exists($filename = NAGVIS_CONFIG_PATH . 'maps/' . $this->data["nagvis_map_name"] . '.cfg') || ($mapConfig = file_get_contents($filename)) === FALSE) {
            return false;
        }

        if (preg_match('/^map_image=([0-9a-z\s\_\.\-\/\\\]*)$/smiu', $mapConfig, $matches) === FALSE) {
            return false;
        }

        $this->data["nagvis_data"]["nagvis_image_name"] = $matches[1];

        if (!isset($this->data["nagvis_thumb_url"]) || !$this->data["nagvis_thumb_url"]) {
            $this->data["nagvis_thumb_url"] = NAGVIS_IMAGES_URL . $this->data["nagvis_data"]["nagvis_image_name"];
        }

        preg_match_all('/define (service|textbox) \{\n([^\}]+)\n\}/smiu', $mapConfig, $matches);

        // definitions from NagVis map
        $definitions = array();
        foreach ($matches[self::NAGVIS_REGEX_PART_DEFINITION] as $key => $match) {
            $definition = array(
                'type' => $matches[self::NAGVIS_REGEX_PART_TYPE][$key]
            );
            preg_match_all('/(^[0-9a-z\s\_\.\-\/\\\]+=[0-9a-zа-яёіїє\'\s\_\.\-\/\\\]+$)+/smiu', $match, $serviceMatches);

            foreach ($serviceMatches[1] as $pair) {
                list($key, $value) = explode('=', $pair);
                $definition[$key] = $value;
            }

            if (isset($definition['host_name']) && isset($definition['service_description'])) {
                $hashKey = $definition['host_name'] . $definition['service_description'];
            } else {
                $hashKey = $definition['object_id'];
            }

            $definition['md5'] = hashKey($hashKey);

            $definitions[] = $definition;
        }

        $keys = array();
        foreach ($definitions as $key => $definition) {
            $keys[] = $definitions[$key]['md5'] . '-' . $key;
        }

        $this->data["nagvis_map_config"] = ($keys) ? array_combine($keys, $definitions) : array();

        unset($definitions);

        return true;
    }

    public function loadNagiosLog($filters = array())
    {
        // prepare filters
        $filters = array_merge($this->filtersBase, $this->filters, $filters);

        $nagiosFilters = array();

        foreach ($filters as $filter) {
            $nagiosFilters[$filter["field"]] = array($filter["value"], $filter["op"]);
        }

        $this->data["nagiosLog"] = $this->livestatusModel->getLog($nagiosFilters);

        return $this;
    }

    abstract protected function _loadNagiosData();

    abstract public function importFromNagios($data);
    
    abstract protected function _install();
}