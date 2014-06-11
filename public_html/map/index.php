<?php
/**
 * Application Entry Point
 *
 * All website functions should go thought this code, and depends from $page
 * we can include needed controller
 *
 *
 */

//include('gzip.php');

ob_start();

define('REQUEST_INTERVAL', 5000);

// set include path to document root
set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT']);
// include configs
require_once('../../inc/config.php');
require_once('../../inc/dbconfig.php');
require_once('../../inc/nagios.php');

require_once('../../lib/lib.inc.php');
require_once('../../lib/functions.php');

require_once('../../inc/autoloader.php');

// connect to DB
require_once('../../inc/model/db.class.php');

try {
	$globalDB = new dbModel('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(PDO::ATTR_PERSISTENT => false, PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
	$globalDB->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$stmt = $globalDB->prepare("SET NAMES utf8");
	$stmt->execute();
	
	$GLOBALS["DATABASES"]["mysql"] = $globalDB;
} catch (PDOException $e) {
	die('DB CONNECTION FAILED IN SOVA ('. $e->getMessage() .')');
}

// include Templates library
require_once('../../lib/smarty/Smarty.class.php');
require_once('../../inc/lib/smarty/SovaSmarty.class.php');
$smarty = new SovaSmarty();

if (is_object($smarty)) {
	$smarty->compile_dir           = CACHE_PATH . "smarty/templates_c";
	$smarty->cache_dir             = CACHE_PATH . "smarty";

    $smarty->addPluginsDir(INC_PATH . "lib/smarty/plugins");

    $smarty->setTemplateDir(LAYOUT_PATH . DEFAULT_LAYOUT_NAME . "/templates")
        ->addTemplateDir(LAYOUT_PATH . BASE_LAYOUT_NAME . "/templates");
	
	if (isset($_SESSION["user"])) $smarty->assign("user", $_SESSION["user"]);
	if (isset($_SESSION["operator"])) $smarty->assign("operator", $_SESSION["operator"]);
}

$GLOBALS["singletones"]["smarty"] = $smarty;

if (isset($_SERVER['REDIRECT_URL'])) {
    $url = $_SERVER['REDIRECT_URL'];
    $rewriteBase = str_replace('/','\/', REWRITE_BASE);
    $matches = array();
    if (preg_match('/^'.$rewriteBase.'([A-Za-z]+)\/([A-Za-z]+)\/number\/([0-9]+)$/', $url, $matches)) {
        $_GLOBALS['route']['page'] = $matches[1];
        $_GLOBALS['route']['action'] = $matches[2];
        $_REQUEST['number'] = $matches[3];
    } elseif (preg_match('/^'.$rewriteBase.'([A-Za-z0-9]+)\/([A-Za-z0-9]+)\/id\/(.*)$/', $url, $matches)) {
        $_GLOBALS['route']['page'] = $matches[1];
        $_GLOBALS['route']['action'] = $matches[2];
        $_REQUEST['id'] = $matches[3];
    } elseif (preg_match('/^'.$rewriteBase.'([A-Za-z0-9]+)\/([A-Za-z0-9]+)\/?/', $url, $matches)) {
        $_GLOBALS['route']['page'] = $matches[1];
        $_GLOBALS['route']['action'] = $matches[2];
    } elseif (preg_match('/^'.$rewriteBase.'([A-Za-z0-9]+)\/?/', $url, $matches)) {
        $_GLOBALS['route']['page'] = $matches[1];
    }
}

$_GLOBALS['route']['page'] = 'map';

/**
 *
 * This code include controller class and launch 3 methods:
 * - preDispatch
 * - [action]Action
 * - postDispatch
 *
 */
if (isset($_GLOBALS['route']['page'])) {
    $page = trim(substr($_GLOBALS['route']['page'], 0, 30));
    $page = strtolower($page);
} else {
    $page = 'index';
}

try {

    if (!preg_match('/^[a-z]+$/', $page)) {
        throw new MissingException('CRITICAL ERROR: Page not found');
    }

    $controllerName = $page . 'Controller';
    
    if (!file_exists(INC_PATH . "controller/{$page}.class.php")) {
	    throw new MissingException('CRITICAL ERROR: Page not found');
    }

    $controller = new $controllerName();
    if (!isset($_REQUEST['xjxr'])) {
        if (isset($_GLOBALS['route']['action'])) {
            $action = trim(substr($_GLOBALS['route']['action'], 0, 30));
            $action = strtolower($action);
            if ($action == '') {
                $action = 'index';
            } elseif (!preg_match('/^[a-z0-9]+$/', $action))
                throw new MissingException('CRITICAL ERROR: Action not found');
        } else {
            $action = 'index';
        }

        if (method_exists($controller, $methodName = $action . 'Action')) {
            $controller->preDispatch();
            $controller->$methodName();
            $controller->postDispatch();
        } else {
            basicController::httpError(404);
            die;
        }
    }
} catch (MissingException $e) {
    basicController::httpError(404);
    ob_flush();
    die;
} catch (Exception $e) {
    basicController::httpError(900, $e->getMessage());
    basicController::logError($e);
    ob_flush();
    die;
};

ob_flush();
