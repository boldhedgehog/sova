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
require_once('../inc/config.php');
require_once('../inc/dbconfig.php');
require_once('../inc/nagios.php');

require_once('../lib/lib.inc.php');
require_once('../lib/functions.php');

require_once('../inc/autoloader.php');

session_start();

// connect to DB
require_once('../inc/model/db.class.php');

try {
    $globalDB = new dbModel('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(PDO::ATTR_PERSISTENT => false, PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
    $globalDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $globalDB->prepare("SET NAMES utf8");
    $stmt->execute();

    $GLOBALS["DATABASES"]["mysql"] = $globalDB;
} catch (PDOException $e) {
    die('DB CONNECTION FAILED IN SOVA (' . $e->getMessage() . ')');
}

//print_r($_SERVER);
//var_dump($_SESSION["operator"]);

// include XAJAX library
//require_once('../lib/xajax_core/xajax.inc.php');
//$xajax = new xajax();
////$xajax->setFlag('debug',true);
//$xajax->configure("javascript URI", "/sova/js/");

//$GLOBALS["singletones"]["xajax"] = $xajax;

// include Templates library
require_once('../lib/smarty/Smarty.class.php');
require_once('../inc/lib/smarty/SovaSmarty.class.php');

/** @var $smarty SovaSmarty */
$smarty = new SovaSmarty();

if (is_object($smarty)) {
    $smarty->setCompileDir(CACHE_PATH . 'smarty/templates_c');
    $smarty->setCacheDir(CACHE_PATH . 'smarty');

    $smarty->setTemplateDir(LAYOUT_PATH . DEFAULT_LAYOUT_NAME . "/templates")
        ->addTemplateDir(LAYOUT_PATH . BASE_LAYOUT_NAME . "/templates");

    $smarty->addPluginsDir(INC_PATH . "lib/smarty/plugins");

    if (isset($_SESSION["user"])) $smarty->assign("user", $_SESSION["user"]);
    if (isset($_SESSION["operator"])) $smarty->assign("operator", $_SESSION["operator"]);
}

$GLOBALS["singletones"]["smarty"] = $smarty;

if (isset($_SERVER['REDIRECT_URL']) || isset($_SERVER['REQUEST_URI'])) {
    if (isset($_SERVER['REDIRECT_URL'])) {
        $url = $_SERVER['REDIRECT_URL'];
    } else {
        $url = $_SERVER['REQUEST_URI'];
        list ($url,) = explode('?', $url, 2);
        $url = urldecode($url);
    }

    $rewriteBase = str_replace('/', '\/', REWRITE_BASE);
    $matches = array();
    if (preg_match('/^' . $rewriteBase . '([A-Za-z]+)\/([A-Za-z]+)\/number\/([0-9]+)$/', $url, $matches)) {
        $_GLOBALS['route']['page'] = $matches[1];
        $_GLOBALS['route']['action'] = $matches[2];
        $_REQUEST['number'] = $matches[3];
    } elseif (preg_match('/^' . $rewriteBase . '([A-Za-z0-9]+)\/([A-Za-z0-9]+)\/id\/(.*)$/', $url, $matches)) {
        $_GLOBALS['route']['page'] = $matches[1];
        $_GLOBALS['route']['action'] = $matches[2];
        $_REQUEST['id'] = $matches[3];
    } elseif (preg_match('/^' . $rewriteBase . '([A-Za-z0-9]+)\/([A-Za-z0-9]+)\/?/', $url, $matches)) {
        $_GLOBALS['route']['page'] = $matches[1];
        $_GLOBALS['route']['action'] = $matches[2];
    } elseif (preg_match('/^' . $rewriteBase . '([A-Za-z0-9]+)\/?/', $url, $matches)) {
        $_GLOBALS['route']['page'] = $matches[1];
    }
}

/**
 *
 * This code include controller class and launch 3 methods:
 * - preDispatch
 * - [action]Action
 * - postDispatch
 *
 */
if (!isset($_SESSION["user"]) || !$_SESSION["user"]) {
    $page = 'operator';
    $_GLOBALS['route']['action'] = 'login';
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
} elseif (isset($_GLOBALS['route']['page'])) {
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

    /** @var $controller basicController */
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
            $response = $controller->$methodName();

            // TODO: create response class
            if ($response instanceof stdClass) {
                echo json_encode($response);
                ob_flush();
                exit;
            }

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


//$xajax->processRequest();

ob_flush();
