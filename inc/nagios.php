<?php
/**
* MK livestatus connection socket
*/

if (preg_match('/\.local$/', $_SERVER['SERVER_NAME'])) {
    define('MKLIVE_SOCKET_ADDRESS', '127.0.0.1:6557');
} else {
    define('MKLIVE_SOCKET_ADDRESS', '/var/lib/nagios3/rw/live:0');
}


/**
 * Nagios constants
 *
 *
 */
define('NAGIOS_WEB_URL', '/nagios3/');
define('NAGIOS_CGIBIN_URL', '/nagios3/cgi-bin/');

define('NAGVIS_WEB_URL', 'https://sova-monitor.net/nagvis/');
define('NAGVIS_MAP_URL', NAGVIS_WEB_URL . 'frontend/nagvis-js/index.php?mod=Map&act=view&show=%s');
//define('NAGVIS_IMAGES_URL', NAGVIS_WEB_URL . 'userfiles/images/maps/');
define('NAGVIS_IMAGES_URL', SOVA_BASE_URL . 'media/maps/');
define('NAGVIS_PATH', '/usr/local/nagvis');
define('NAGVIS_CONFIG_PATH', NAGVIS_PATH . '/etc/');
define('NAGVIS_SHARE_PATH', NAGVIS_PATH . '/share/');
define('NAGVIS_MAP_IMAGES_PATH', NAGVIS_PATH . '/share/userfiles/images/maps/');

define('STOROZH_SERVICE_GROUP', 'storozh-services');
define('STOROZH_HOST_GROUP', 'storozh-hosts');

define('STOROZH_OPERATOR_GROUP', 'operators');
