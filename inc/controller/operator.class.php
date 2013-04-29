<?php

/**
 * This file contain operatorController class.
 * This class can handle operator login
 *
 *
 *
 * @copyright  2010
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */
class operatorController extends basicController
{

    /* @var operatorModel */
    protected $operatorModel = false;
    /* @var onlineOperatorModel */
    protected $onlineOperatorModel = false;
    /* @var array */
    protected $operatorInfo = null;

    public function __construct()
    {
        $this->operatorModel = new operatorModel();
        $this->onlineOperatorModel = new onlineOperatorModel();
        $this->livestatusModel = new mklivestatusModel();

        $this->_getCurrentOperator();

        return parent::__construct();
    }

    protected function _getCurrentOperator()
    {
        if ($this->operatorInfo) {
            return $this->operatorInfo;
        }

        return $this->operatorInfo = isset($_SESSION['operator']) ? $_SESSION['operator'] : null;
    }

    /**
     * Get loaded operator.
     *
     * @return array
     */
    public function getOperatorInfo()
    {
        return $this->operatorInfo;
    }

    public function indexAction()
    {
        self::httpError(404);
    }

    public function loginAction()
    {
        if (isset($_SESSION["logout required"]) && $_SESSION["logout required"] && $_SERVER["PHP_AUTH_USER"]) {
            unset($_SESSION["logout required"]);
            header('WWW-Authenticate: Basic realm="SOVA Access"');
            self::httpError(401);
            self::log("Requesting new HTTP basic auth to logout {$_SERVER["PHP_AUTH_USER"]}");
            die;
        }

        // get current logged in contact
        $_SESSION["user"] = $this->livestatusModel->getContactByName($_SERVER["PHP_AUTH_USER"]);
        //session_destroy();
        if (!$_SESSION["user"]) {
            session_destroy();
            basicController::httpError(403);
            self::logError("Unknown user {$_SERVER["PHP_AUTH_USER"]}");
            die;
        }

        unset($_SESSION["operator"]["status"]);
        // try to register as operator
        if (!isset($_SESSION["operator"]["operator_id"]) || !$_SESSION["operator"]["operator_id"]) {
            $operator = $this->operatorModel->loadByNagiosName($_SERVER["PHP_AUTH_USER"])->getData();

            $currentDate = date("Y-m-d");
            if (!$operator || !$operator["enabled"] || $operator["enabled_from"] > $currentDate || $operator["enabled_to"] < $currentDate) {
                session_destroy();
                //basicController::httpError(403, "Оператор не існує, або відключений.");
                self::logError("Not registered / disabled / outdated operator {$_SERVER["PHP_AUTH_USER"]}");
                header('WWW-Authenticate: Basic realm="SOVA Access"');
                self::httpError(401);
                die();
            } else {
                $this->onlineOperatorModel->login($operator["operator_id"], time());
                self::logAction('operatorModel', 'login', $operator["operator_id"], null, $operator["operator_id"], 'Operator login');
                self::log("Logging in {$operator["name"]}");
            }
            if (isset($operator['settings']) && is_string($operator['settings'])) {
                $operator['settings'] = unserialize($operator['settings']);
            } else {
                $operator['settings'] = array();
            }

            $operator['form_key'] = md5(SALT . md5(time()));

            $this->operatorInfo = $operator;

            $_SESSION["operator"] = $operator;
        } else {
            $operator = $_SESSION["operator"];
            $this->onlineOperatorModel->refresh($operator["operator_id"], time());
        }

        if (is_array($_SESSION["operator"])) {
            $_SESSION["operator"]["status"] = $this->onlineOperatorModel->getStatus($operator["operator_id"]);
        }

        if (isset($_SESSION["redirect_url"])) {
            $url = $_SESSION["redirect_url"];
            unset($_SESSION["redirect_url"]);
            // do not redirect to myself
            if (SOVA_BASE_URL . 'operator/login/' != $url) {
                self::redirect($url);
            }
        }

        self::redirect(SOVA_BASE_URL);
    }

    public function logoutAction()
    {
        /*if ($_SESSION["logout required"] && $_SERVER["PHP_AUTH_USER"]) {
            unset($_SESSION["logout required"]);
            self::redirect(SOVA_BASE_URL);
        }*/

        $redirectUrl = SOVA_BASE_URL . 'operator/login/';

        if ($_SESSION["operator"]["operator_id"]) {
            $this->onlineOperatorModel->logout($_SESSION["operator"]["operator_id"]);
            self::logAction('operatorModel', 'logout', $_SESSION["operator"]["operator_id"], NULL, $_SESSION["operator"]["operator_id"], 'Operator logout');
            self::log("Logging out {$_SESSION["operator"]["name"]}");
        }

        $this->operatorInfo = null;

        unset($_SESSION["operator"]);
        unset($_SESSION["user"]);

        if (isset($_SESSION["logout required"]) && $_SESSION["logout required"]) {
            self::redirect($redirectUrl);
        }

        $this->smarty->assign('url', $redirectUrl);
        $this->smarty->display('logout.tpl');

        $_SESSION["logout required"] = true;
    }

    public function importAction()
    {
        $data = $this->livestatusModel->getOperators();
        if (!$data) {
            throw new mklivestatusException("No contacts found");
        }

        $this->operatorModel->importFromNagios($data);

        echo "done";
    }

}