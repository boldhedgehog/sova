#!/usr/bin/php
<?php
define('MKLIVE_SOCKET_ADDRESS', '/var/lib/nagios3/rw/live');

    $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
    if (!$socket) die("cannot create");
    $result = socket_connect($socket, MKLIVE_SOCKET_ADDRESS);
    if (!$result) die("cannot connect");
    
    /*socket_write($socket, "GET services\nFilter: groups >= storozh-services\nOutputFormat:json\nKeepAlive: on\nResponseHeader: fixed16\n\n");
    
    $read = readSocket($socket, 16);
    // Extract status code
    $status = substr($read, 0, 3);
    // Extract content length
    $len = intval(trim(substr($read, 4, 11)));
    // Read socket until end of data
    $read = readSocket($socket, $len);
    
    echo "<pre>";
    print_r(json_decode($read));
    echo "</pre>";*/
    
    socket_write($socket, "GET log\nFilter: time > ".strtotime("-1 day")."\nFilter: class = 1\nFilter: current_service_groups >= storozh-services\nAnd: 3\nWaitTrigger: state\nOutputFormat:json\nKeepAlive: on\nResponseHeader: fixed16\n\n");
    //socket_write($socket, "GET columns\nFilter: table = log\nOutputFormat:json\nKeepAlive: on\nResponseHeader: fixed16\n\n");
    
    $read = readSocket($socket, 16);
    // Extract status code
    $status = substr($read, 0, 3);
    // Extract content length
    $len = intval(trim(substr($read, 4, 11)));
    // Read socket until end of data
    $read = readSocket($socket, $len);
    
    if($status!="200") die($read);
    
    echo "<pre>";
    print_r(json_decode($read));
    echo "</pre>";
                                                                                    
    
            /**
         * PRIVATE readSocket()
         *
         * Method for reading a fixed amount of bytest from the socket
         *
         * @param   Integer  Number of bytes to read
         * @return  String   The read bytes
         * @author  Lars Michelsen <lars@vertical-visions.de>
         */
        function readSocket($socket, $len) {
                $offset = 0;
                $socketData = '';
                while($offset < $len) {
                        if(($data = @socket_read($socket, $len - $offset)) === false) {
                                return false;
                        }
                        if(($dataLen = strlen($data)) === 0) {
                                break;
                        }
                        $offset += $dataLen;
                        $socketData .= $data;
                }
                return $socketData;
        }

?>
