<?php

/**
 * This file contain basicController class.
 *
 *
 */

require_once('smarty/Smarty.class.php');
require_once('xajax_core/xajax.inc.php');

class basicController
{

    /* @var $smarty SovaSmarty */
    protected $smarty = false;

    /* @var xajax xajax */
    protected $xajax = false;

    /* @var $objResponse xajaxResponse */
    protected $objResponse;

    /** @var stdClass */
    protected $jsonResponse;

    /* @var $sessionNotifications array */
    protected $sessionNotifications = null;

    /* @var array */
    protected $operatorInfo = null;

    protected $requestUri;

    /**
     * HTML error codes
     */
    protected static $errorCodes = array(
        100 => "Continue",
        101 => "Switching Protocols",
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Moved Temporarily",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required 403 Forbidden",
        403 => "Access Denied",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Time-Out",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Request Entity Too Large",
        414 => "Request-URL Too Large",
        415 => "Unsupported Media Type",
        500 => "Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Out of Resources",
        504 => "Gateway Time-Out",
        505 => "HTTP Version not supported"

    );

    const errorFileName = "error.log";
    const generalFileName = "general.log";

    public function __construct()
    {
        $this->xajax = $GLOBALS["singletones"]["xajax"];
        $this->smarty = $GLOBALS["singletones"]["smarty"];

        $this->requestUri = self::getEnvVar('REQUEST_URI');

        $this->xajax->register(XAJAX_CALLABLE_OBJECT, $this);
        $this->smarty->assignByRef("controller", $this);
        $this->smarty->assign("controllerName", get_class($this));

        $this->sessionNotifications = self::getSessionNotifications();
        self::clearSessionNotifications();

        // init session
        if (!isset($_SESSION["sova"])) $_SESSION["sova"] = NULL;
        if (!isset($_SESSION["sova"]["alerts"])) $_SESSION["sova"]["alerts"] = NULL;

        return $this;
    }

    /**
     * This function will be executed before each request
     * @return basicController $this
     */
    public function preDispatch()
    {
        if ($this->smarty) {
            $this->smarty->assign('sessionNotifications', $this->sessionNotifications);
            if ($this->xajax) {
                $this->smarty->assign('xajax_javascript', $this->xajax->getJavascript());
            }
        }
        return $this;
    }

    /**
     * This function will be executed after each request
     * @return basicController $this
     */
    public function postDispatch()
    {
        return $this;
    }

    /**
     * Get Smarty object
     * @return Smarty the Smarty object
     */
    public function getSmarty()
    {
        return $this->smarty;
    }

    /**
     * Get xajax object
     * @return xajax the XAJAX object
     */
    public function getXajax()
    {
        return $this->xajax;
    }

    public function getRequestUri()
    {
        return $this->requestUri;
    }

    public function __toString()
    {
        return get_class($this);
    }

    // ------------------------   Static methods  ------------------------------------

    public static function jsEscapeString($string)
    {
        $escape = array('\\' => "\\\\", "\r\n" => '\n', "\r" => '\n', "\n" => '\n', '"' => '\"', "'" => "\\'");

        return str_replace(array_keys($escape), array_values($escape), $string);
    }

    public static function getTimeStamp($MySqlDate)
    {
        $date_array = explode("-", $MySqlDate);
        $var_year = $date_array[0];
        $var_month = $date_array[1];
        $var_day = $date_array[2];
        $var_timestamp = mktime(0, 0, 0, $var_month, $var_day, $var_year);

        return $var_timestamp;
    }


    /**
     *
     * @param basicModel|string $object
     * @param string $action
     * @param integer $operator_id
     * @param integer $timestamp
     * @param integer $id
     * @param string $description
     */
    protected static function logAction($object, $action, $operator_id, $timestamp = NULL, $id = NULL, $description = NULL)
    {
        if ($timestamp === NULL) $timestamp = time();

        if (is_object($object)) {
            if ($id === NULL) {
                $id = $object->getId();
            }
            $object = get_class($object);
        }

        $data[':object'] = $object;
        $data[':method'] = $action;
        $data[':operator_id'] = $operator_id;
        $data[':timestamp'] = $timestamp;
        $data[':description'] = $description;
        $data[':object_id'] = $id;
        $data[':referer'] = self::getEnvVar('HTTP_REFERER');
        $data[':ip'] = self::getEnvVar('REMOTE_ADDR');
        $data[':user_agent'] = self::getEnvVar('HTTP_USER_AGENT');

        try {
            logModel::logAction($data);
        } catch (Exception $e) {
            self::logError($e);
        }
    }

    public static function logError($error)
    {
        $message = "";
        if ($error instanceof Exception) {
            $message = "[" . $error->getCode() . "] " . $error->getMessage() . " in " . $error->getFile() . ":" . $error->getLine();
        } else {
            $message = $error;
        }

        return self::log($message, self::errorFileName);
    }

    public static function log($message, $filename = self::generalFileName)
    {
        $fp = fopen(LOG_PATH . $filename, "a");
        if (!$fp)
            return false;

        fwrite($fp, date(DATE_W3C, time()) . " $message\n");

        fclose($fp);

        return true;
    }

    /**
     *
     * @param string $code
     * @param string $message
     */
    public static function httpError($code, $message = false)
    {
        $message = $message ? $message : (self::$errorCodes[$code]);
        if (!headers_sent()) {
            header("HTTP/1.1 $code $message");
        }
        /* @var $smarty Smarty */
        $smarty = $GLOBALS["singletones"]["smarty"];
        $smarty->assign("code", $code);
        $smarty->assign("message", $message);

        $smarty->display("error.tpl");

        ob_flush();
    }

    /**
     *
     * @param string $message
     * @param string $type
     */
    public static function sessionNotification($message, $type = "notification")
    {
        $_SESSION["sova"]["alerts"][] = array("type" => $type, "message" => $message);
    }

    public static function getSessionNotifications()
    {
        return isset($_SESSION["sova"]["alerts"]) ? $_SESSION["sova"]["alerts"] : NULL;
    }

    public static function clearSessionNotifications()
    {
        if (isset($_SESSION["sova"]["alerts"])) {
            unset($_SESSION["sova"]["alerts"]);
        }
    }

    public static function redirect($url)
    {
        if (headers_sent()) {
            echo "<script language=\"javascript\">window.location.href=\"$url\"</script>";
        } else {
            header("Location: $url");
        }
        die();
    }

    /**
     * Returns $_SERVER variable value
     * @param string $var
     * @param null $default
     * @return string
     */
    public static function getEnvVar($var, $default = NULL)
    {
        return (isset($_SERVER[$var])) ? $_SERVER[$var] : $default;
    }

    /**
     * Returns $_REQUEST variable value
     * @param string $var
     * @param null $default
     * @return string
     */
    public static function getRequestVar($var, $default = NULL)
    {
        return (isset($_REQUEST[$var])) ? $_REQUEST[$var] : $default;
    }

    public static function getCookie($var, $default = NULL)
    {
        return (isset($_COOKIE[$var])) ? $_COOKIE[$var] : $default;
    }

    public function setCookie($name, $value) {
        // do not set cookies if it's an AJAX response
        if ($this->objResponse) {
            return;
        }
        if (is_array($value) || is_object($value)) {
            $value = self::jsEscapeString(self::jsonEncode($value));
        }
        SetCookie($name, $value);
    }

    public static function jsonEncode($value, $options = 0) {
        return json_encode($value, $options);
    }

    public static function jsonDecode($json, $assoc = null) {
        return json_decode($json, $assoc);
    }

    public static function isMobile(){
        return isset($_SERVER['IS_MOBILE']) && $_SERVER['IS_MOBILE'];
    }

    public function getCurrentOperator()
    {
        if ($this->operatorInfo) {
            return $this->operatorInfo;
        }

        return $this->operatorInfo = isset($_SESSION['operator']) ? $_SESSION['operator'] : null;
    }

}

