<?php
/**
 * Main common for all subsytems config
 *
 *
*/

define('SOVA_VERSION', '0.7.1');

date_default_timezone_set('Europe/Kiev');

/*if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    if (isset($_REQUEST['debug_use_proxy'])) {
        if ($_REQUEST['debug_use_proxy'] == 'on') {
            $ip_idx = 1;
        } else {
            $ip_idx = $_REQUEST['debug_use_proxy'];
        }
    } else {
        $ip_idx = 0;
    }
    $address = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $_SERVER['REMOTE_ADDR'] = $address[$ip_idx];
}*/

define('SITE_NAME', 'Інтелект');

// The site root
define('SITE_ROOT', realpath(dirname(__file__).'/../public_html').'/');

// The path from the site root to the include dir
define('INC_PATH', SITE_ROOT . '../inc/');

// The path from the site root to the library dir
define('LIBRARY_PATH', SITE_ROOT . '../lib/');

define('CACHE_PATH', SITE_ROOT . '../var/cache/');
define('LOG_PATH', SITE_ROOT . '../var/log/');

// path to the layout root
define('LAYOUT_PATH', SITE_ROOT . '../layout/');

/**
 * Layout switcher
 */
define('BASE_LAYOUT_NAME', 'default');

if (isset($_SERVER['IS_MOBILE']) && $_SERVER['IS_MOBILE'] ||
    isset($_COOKIE['IS_MOBILE']) && $_COOKIE['IS_MOBILE']) {
    define('DEFAULT_LAYOUT_NAME', 'mobile');
} else {
    define('DEFAULT_LAYOUT_NAME', 'default');
}

define('SALT', '830fe341e80e4004a338f2473ecfe268');

define('REWRITE_BASE', '/sova/');
define('SOVA_PATH_ROOT', $_SERVER['DOCUMENT_ROOT'].REWRITE_BASE);

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
	define('SOVA_BASE_URL',	'https://'.$_SERVER['HTTP_HOST'].REWRITE_BASE);
} else {
	define('SOVA_BASE_URL',	'http://'.$_SERVER['HTTP_HOST'].REWRITE_BASE);
}

// set the include path to the files
// safe mode might fail on this.
ini_set('include_path', SITE_ROOT . INC_PATH . ':' . LIBRARY_PATH . ':' . ini_get('include_path'));

/**
 * Cookie settings.
 *
 * Bellow all config parameters for COOKIE usage are defines.
 * These settings will be applied to sessions automaticly and should be applyed to
 * other setcookie() calls (cannot be defaulted).
 **/

define ('SOVA_COOKIE_PATH','/sova/');
define ('SOVA_COOKIE_DOMAIN',$_SERVER['HTTP_HOST']);

session_set_cookie_params(0,SOVA_COOKIE_PATH,((false === strpos(SOVA_COOKIE_DOMAIN,'.'))?null:SOVA_COOKIE_DOMAIN));
session_name('sova-fe');

define('DATE_SOVA', 'Y-m-d');
define('DATE_SOVA_TIME', 'H:i:s');
define('DATE_SOVA_DATETIME', 'Y-m-d H:i:s');

define('LAYOUT_IMAGES_URL', SOVA_BASE_URL . 'skin/' . DEFAULT_LAYOUT_NAME .'/images/');
define('LAYOUT_LOGOS_URL', SOVA_BASE_URL . 'skin/' . DEFAULT_LAYOUT_NAME .'/images/logos/');
define('LAYOUT_CSS_URL', SOVA_BASE_URL . 'skin/' . DEFAULT_LAYOUT_NAME .'/css/');

$GLOBALS["IMAGEMODIFIER_CHAINS"]["host-scheme-thumb"] = array(array("resize", array("width" => 256)));
$GLOBALS["IMAGEMODIFIER_CHAINS"]["scheme_mobile"] = array(array("resize", array("width" => 290)));
$GLOBALS["IMAGEMODIFIER_CHAINS"]["host-scheme-ymap"] = array(array("resize", array("width" => 128, "height" => 128)));
$GLOBALS["IMAGEMODIFIER_CHAINS"]["host-registry-thumb"] = array(array("resize", array("width" => 64)));