<?php

/**
 * This file contain mklivestatusModel class.
 * This class contain methods for getting data from Nagios' mllivestatus socket.
 *
 *
 * @copyright  2010
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */
//define('MKLIVE_SOCKET_ADDRESS', '/var/lib/nagios3/rw/live');
//define('MKLIVE_SOCKET_ADDRESS', '127.0.0.1:6557');

class socketException extends Exception
{

    function __construct($message)
    {
        $errno = socket_last_error();
        $errmsg = socket_strerror(socket_last_error());

        return parent::__construct("$message ($errmsg)", $errno);
    }

}

class mklivestatusException extends Exception
{

}

class mklivestatusModel extends basicModel
{
    const MKLIVE_RESPONSE_TYPE_JSON = 0;
    const MKLIVE_RESPONSE_TYPE_ARRAY = 1;
    const MKLIVE_RESPONSE_TYPE_OBJECT = 2;
    const MKLIVE_RESPONSE_TYPE_ASSOC = 4;

    const HOST_IMAGE_PLACEHOLDER = "logos/host.gif";

    /*
     * 0 - All messages not in any other class
     * 1 - host and service alerts
     * 2 - important program events (program start, etc.)
     * 3 - notifications
     * 4 - passive checks
     * 5 - external commands
     * 6 - initial or current state entries
     * 7 - program state change
     */
    const NAGIOS_LOG_CLASS_OTHER = 0;
    const NAGIOS_LOG_CLASS_ALERT = 1;
    const NAGIOS_LOG_CLASS_EVENT = 2;
    const NAGIOS_LOG_CLASS_NOTIFICATION = 3;
    const NAGIOS_LOG_CLASS_PASSIVE_CHECK = 4;
    const NAGIOS_LOG_CLASS_EXTERNAL_COMMAND = 5;
    const NAGIOS_LOG_CLASS_INITIAL_STATE = 6;
    const NAGIOS_LOG_CLASS_PROGRAM_STATE = 7;

    const NAGIOS_LOG_TYPE_SERVICE_ALERT = "SERVICE ALERT";
    const NAGIOS_LOG_TYPE_HOST_ALERT = "HOST ALERT";

    /**
     * Contact to be filtered by
     * @var mixed
     */
    protected $contactName = false;
    protected $socket;
    protected $commonServiceColumns = "acknowledged acknowledgement_type description display_name notes notes_expanded state state_type groups host_name host_alias host_state host_state_type host_groups host_notes last_check last_state last_state_change plugin_output comments comments_with_info is_flapping custom_variable_names custom_variable_values";
    protected $shortServiceColumns = "description display_name notes notes_expanded state state_type groups host_name last_check last_state plugin_output custom_variable_names custom_variable_values";
    protected $commonHostColumns = "name alias address state hard_state state_type groups icon_image icon_image_expanded icon_image_alt services services_with_state notes notes_expanded total_services worst_service_hard_state worst_service_state custom_variable_names custom_variable_values";
    protected $commonLogColumns = "attempt class command_name comment host_name lineno message options plugin_output service_description state state_type time type current_service_notes_expanded";
    protected $logAllowedFilters = array("time", "host_name", "service_description", "state", "class", "type", "state_type");

    public function __construct()
    {
        $this->_connectToSocket();
    }

    public function __destruct()
    {
        if (is_resource($this->socket)) {
            socket_close($this->socket);
            //socket_get_status()
        }
    }

    public function getContacts()
    {
        return $this->_executeQuery("GET contacts");
    }

    public function getContactByName($name)
    {
        $contact = $this->_executeQuery("GET contacts\nFilter: name = " . addslashes($name));

        return is_array($contact) ? $contact[0] : false;
    }

    public function getServices($serviceName = NULL)
    {
        return $this->_executeQuery("GET services\nColumns: $this->commonServiceColumns" . (($serviceName)
                                            ? ("\nFilter: description = " . addslashes($serviceName)) : ""));
    }

    public function getHosts($hostName = NULL)
    {
        return $this->_executeQuery("GET hosts\nColumns: $this->commonHostColumns" . (($hostName)
                                            ? ("\nFilter: name = " . addslashes($hostName)) : ""));
    }

    /**
     * @param $filters array - Array with (field => value) filters. Condition is AND. 'time' will be set to the start of the current day for sanity reason
     *
     * @return array|mixed|String
     */
    public function getLog($filters = array())
    {
        $nagiosFilters = array();

        if (!isset($filters['time'])) {
            $filters['time'] = array(mktime(0, 0, 0), '>=');
        }

        foreach ($filters as $key => $value) {
            if ($key == 'time_start' || $key == 'time_end') {
                $key = 'time';
            }
            if (!in_array($key, $this->logAllowedFilters)) {
                unset($filters[$key]);
                continue;
            }
            $operator = '=';

            if (is_array($value)) {
                list($value, $operator) = $value;
            }

            $nagiosFilters[] = "Filter: $key $operator $value";
        }

        return $this->_executeQuery("GET log\nColumns: $this->commonLogColumns\n" . implode("\n", $nagiosFilters));
    }

    public function getOverview($hostName = null, $loadServices = true)
    {
        $query = "GET hosts\nColumns: $this->commonHostColumns";

        if ($hostName) {
            if (is_array($hostName)) {
                $hostName = array_unique($hostName);

                foreach ($hostName as $name) {
                    $query .= "\nFilter: name = " . addslashes($name);
                }

                if (count($hostName) > 1) {
                    $query .= "\nOr: " . count($hostName);
                }

            } else {
                $query .= "\nFilter: name = " . addslashes($hostName);
            }
        }

        // get all hosts
        $hosts = $this->_executeQuery($query);

        // md5 keys
        $md5keys = array();
        // go through hosts
        foreach ($hosts as $key => $host) {
            $md5keys[] = $hosts[$key]["md5"] = hashKey($host["name"]);

            $hosts[$key]["icon_image_with_status"] = self::getIconWithStatus($host);

            // get services for this host
            if ($loadServices) {
                $services = $this->_executeQuery("GET services\nColumns: $this->commonServiceColumns\nFilter: host_name = {$host["name"]}");
                foreach ($hosts[$key]["services"] as $serviceKey => $service) {
                    $services[$serviceKey]["md5"] = hashKey($services[$serviceKey]["host_name"] . $services[$serviceKey]["description"]);
                    if ($service == $services[$serviceKey]["description"]) {
                        unset($hosts[$key]["services"][$serviceKey]);
                        $hosts[$key]["services"][$services[$serviceKey]["md5"]] = $services[$serviceKey];
                    }
                }
            } elseif (isset($host['services_with_state']) && $host['services_with_state']) {
                // TODO: Move to a separate method
                $services = array();
                foreach ($host['services_with_state'] as $service) {
                    $md5 = hashKey($host['name'] . $service[0]);
                    $services[$md5] = array(
                        'service_description' => $service[0],
                        'state' => $service[1],
                        'md5' => $md5
                    );
                }
                $hosts[$key]['services_with_state'] = $services;
                unset($services);
            }
        }
        
        $hosts = array_combine($md5keys, $hosts);

        return ($hostName && !is_array($hostName)) ? $hosts[$md5keys[0]] : $hosts;
    }

    public function getHostServices($hostName)
    {
        if (!$hostName) {
            return false;
        }

        // get services for this host
        $services = $this->_executeQuery("GET services\nColumns: $this->shortServiceColumns\nFilter: host_name = {$hostName}");
        foreach ($services as $servicekey => $service) {
            $services[$servicekey]["md5"] = hashKey($service["host_name"] . $service["description"]);
        }

        return $services;
    }

    public function getServicesFull($serviceName = null, $hostName = null)
    {
        return $this->_executeQuery("GET services" 
                . (($serviceName)
                ? ("\nFilter: description = " . addslashes($serviceName))
                : "")
                . (($hostName)
                ? ("\nFilter: host_name = " . addslashes($hostName)) : "")
            );
    }

    public function getHostsFull()
    {
        return $this->_executeQuery("GET hosts");
    }

    public function getContactsFull()
    {
        return $this->_executeQuery("GET contacts");
    }

    // TODO: let's hope mk_livestatus will return contacts with groups field in the future
    public function getOperators()
    {
        $groups = $this->_executeQuery("GET contactgroups\nColumns: members\nFilter: name = " . STOROZH_OPERATOR_GROUP);

        $contacts = array();
        foreach ($groups as $key => $group) {
            foreach ($group["members"] as $contact) {
                $contacts[] = $this->getContactByName($contact);
            }
        }

        return $contacts;
    }

    public function getStorozhServices()
    {
        return $this->_executeQuery("GET services\nColumns: $this->commonServiceColumns\nFilter: groups >= " . STOROZH_SERVICE_GROUP);
    }

    public function refreshServices($time)
    {
        return $this->_executeQuery("GET services\nColumns: $this->commonServiceColumns host_worst_service_state host_custom_variable_names host_custom_variable_values\nFilter: last_state_change > $time");
    }

    /**
     * Waits for services, which states changed from non critical to critical
     * @param integer $time UNIX timestamp
     * @param integer $timeout timeout in milliseconds
     *
     * @return array|mixed|String
     */
    public function waitForSovaCriticalServices($time, $timeout = 5000)
    {
        // set max execution time to timeout * 2
        if (($timeoutSec = $timeout / 1000 * 2) < intval(ini_get('max_execution_time'))) {
            ini_set('max_execution_time', intval($timeoutSec));
        }

        $time = intval($time);
        $timeout = intval($timeout);

        return $this->_executeQuery(
            "GET services\n" .
                "Columns: $this->commonServiceColumns\n" .
                "WaitCondition: state = 1\n" .
                "WaitCondition: state = 2\n" .
                "WaitConditionOr: 2\n" .
                "WaitTrigger: state\n" .
                "WaitTimeout: $timeout\n" .
                "Filter: groups >= " . STOROZH_SERVICE_GROUP . "\n" .
                "Filter: last_state_change > $time"
        );
    }

    /**
     * Find services with changed states with CRITICAL and WARNING statuses
     */
    public function getSovaCriticalServices($time)
    {
        return $this->_executeQuery("GET services\nColumns: $this->commonServiceColumns\nFilter: last_state_change > $time\nFilter: state = 1\nFilter: state = 2\nOr: 2\nFilter: last_state != 2\nFilter: acknowledged = 0\nFilter: groups >= " . STOROZH_SERVICE_GROUP);
    }

    public function refreshHosts($time)
    {
        return $this->_executeQuery("GET hosts\nColumns: $this->commonHostColumns\nFilter: last_state_change > $time");
    }

    protected function _connectToSocket()
    {
        // already connected
        if (is_resource($this->socket)) {
            return $this->socket;
        }

        list($address, $port) = explode(':', MKLIVE_SOCKET_ADDRESS);
        $this->socket = ($port) ? socket_create(AF_INET, SOCK_STREAM, SOL_TCP) : socket_create(AF_UNIX, SOCK_STREAM, 0);
        if (!$this->socket)
            throw new socketException("Не удалось создать сокет");

        $result = socket_connect($this->socket, $address, $port);
        if (!$result)
            throw new socketException("Не удалось открыть сокет");

        return $this->socket;
    }

    /**
     *
     * @param String $query
     * @param int $responseType
     * @param bool $applyContactFilter
     * @throws socketException
     * @throws mklivestatusException
     *
     * @return array|mixed|String
     */
    protected function _executeQuery($query, $responseType = self::MKLIVE_RESPONSE_TYPE_ASSOC, $applyContactFilter = true)
    {
        if (!$this->socket) {
            $this->_connectToSocket();
        }

        //basicController::log($query);

        if ($responseType === NULL) {
            $responseType = self::MKLIVE_RESPONSE_TYPE_ASSOC;
        }

        $contactFilter = ($applyContactFilter && $this->contactName) ? "\nAuthUser: $this->contactName" : "";

        if (!socket_write($this->socket, trim(trim($query, "\n")) . "$contactFilter\nColumnHeaders: on\nOutputFormat:json\nKeepAlive: on\nResponseHeader: fixed16\n\n")) {
            throw new socketException("Не удалось записать в сокет");
        }

        if (false === ($read = $this->_readSocket($this->socket, 16))) {
            throw new socketException("Не удалось прочитать сокет");
        }

        // Extract status code
        $status = substr($read, 0, 3);

        // Extract content length
        $len = intval(trim(substr($read, 4, 11)));

        // Read socket until end of data
        if (false === ($read = $this->_readSocket($this->socket, $len))) {
            throw new socketException("Не удалось прочитать сокет");
        }

        if ($status != "200") {
            throw new mklivestatusException($read);
        }

        return ($responseType == self::MKLIVE_RESPONSE_TYPE_JSON) ? $read :
                (($responseType == self::MKLIVE_RESPONSE_TYPE_ARRAY || $responseType == self::MKLIVE_RESPONSE_TYPE_OBJECT)
                        ? json_decode($read, $responseType == self::MKLIVE_RESPONSE_TYPE_ARRAY) :
                        $this->_associateResponse($read));
    }

    /**
     * Replaces numeric keys for result with the names from the 0 result, i.e. the column row
     * @param unknown_type $array
     */
    protected function _associateResponse($response)
    {
        $array = json_decode($response, true);

        if (!$array)
            return array();

        $len = count($array);
        for ($i = 1; $i < $len; $i++) {
            foreach ($array[$i] as $key => $value) {
                $array[$i][$array[0][$key]] = $value;
                unset($array[$i][$key]);
            }
            // merge custom variables with common data
            if (isset($array[$i]["custom_variable_names"]) && ($customVariableNames = $array[$i]["custom_variable_names"])) {
                foreach ($customVariableNames as $key => $value) {
                    $array[$i][$value] = $array[$i]["custom_variable_values"][$key];
                }
            }
            // if this is a service - do the same for host custom variables
            if (isset($array[$i]["host_custom_variable_names"]) && ($customVariableNames = $array[$i]["host_custom_variable_names"])) {
                foreach ($customVariableNames as $key => $value) {
                    $array[$i]["host_$value"] = $array[$i]["host_custom_variable_values"][$key];
                }
            }

            // calculate state duration
            if (isset($array[$i]['last_state_change']) && $array[$i]['last_state_change']) {
                $array[$i]['state_duration'] = time() - intval($array[$i]['last_state_change']);
            } elseif (!isset($array[$i]['class'])) { //we are not trying to get LOG
                // set status to PENDING
                $array[$i]['state'] = nagiosObjectModel::STATE_PENDING;
            }

            // calculate state duration for host
            if (isset($array[$i]['host_last_state_change']) && $array[$i]['host_last_state_change']) {
                $array[$i]['host_state_duration'] = time() - intval($array[$i]['host_last_state_change']);
            } elseif (!isset($array[$i]['class'])) { //we are not trying to get LOG
                // set status to PENDING
                $array[$i]['host_state'] = nagiosObjectModel::STATE_PENDING;
            }

            // set whether this is a storozh object
            if (isset($array[$i]['groups']) && (in_array(STOROZH_SERVICE_GROUP, $array[$i]['groups']) || in_array(STOROZH_HOST_GROUP, $array[$i]['groups']))) {
                $array[$i]['is_storozh'] = true;
            } elseif (isset($array[$i]['SOVA_SERVICE_TYPE']) && in_array($array[$i]['SOVA_SERVICE_TYPE'], array('sensor','button'))) {
                $array[$i]['is_storozh'] = true;
            } else {
                $array[$i]['is_storozh'] = false;
            }

            if (isset($array[$i]['state'])) {
                $array[$i]['state_text'] = nagiosObjectModel::stateCaption($array[$i]['state']);
            }
        }
        array_shift($array);
        return $array;
    }

    static function getIconWithStatus(array $data)
    {
        // if there is host_name, then we probably work with a service
        $prefix = isset($data["host_name"]) ? "host_" : "";
        $icon = (isset($data["{$prefix}SOVA_ICON_IMAGE"]) && $data["{$prefix}SOVA_ICON_IMAGE"])
                ? $data["{$prefix}SOVA_ICON_IMAGE"] : self::HOST_IMAGE_PLACEHOLDER;
        return pathinfo($icon, PATHINFO_FILENAME) . $data["{$prefix}worst_service_state"] . "." . pathinfo($icon, PATHINFO_EXTENSION);
    }

    protected function _getSocket()
    {
        if (!$this->socket)
            return $this->_connectToSocket();
    }

    /**
     * PRIVATE readSocket()
     *
     * Method for reading a fixed amount of bytest from the socket
     *
     * @param   Integer  Number of bytes to read
     * @return  String   The read bytes
     * @author  Lars Michelsen <lars@vertical-visions.de>
     */
    protected function _readSocket($socket, $len)
    {
        $offset = 0;
        $socketData = '';
        while ($offset < $len) {
            if (($data = @socket_read($socket, $len - $offset)) === false) {
                return false;
            }
            if (($dataLen = strlen($data)) === 0) {
                break;
            }
            $offset += $dataLen;
            $socketData .= $data;
        }
        return $socketData;
    }

    // properties functions
    public function setContactFilterName($contactName)
    {
        $this->contactName = is_array($contactName) ? $contactName["name"] : $contactName;
        return $this;
    }

    public function getContactFilterName()
    {
        return $this->contactName;
    }

    public function load($key = NULL)
    {
        throw new ModelException("Method is unavailable for this model");
    }

}