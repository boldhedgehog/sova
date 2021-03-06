<?php

/**
 *
 */
class alertController extends basicController {

    protected $alertModel;

    public function __construct() {
        $this->alertModel = new alertModel();

        $this->smarty = $GLOBALS["singletones"]["smarty"];

        return parent::__construct();
    }

    public function indexAction() {
        $alert = $this->alertModel->getAlertFull($_REQUEST["id"]);

	if (!$alert) {
	    self::httpError(404);
	    die;
	}

	if ($alert instanceof PDOException) {
	    self::logError($alert);
	    self::httpError(404);
	    die;
	    return;
	}
	
	$this->smarty->assign("alert", $alert);
	$this->smarty->display("alert_view.tpl");
    }

    public function processAction() {
        //print_r ($_REQUEST["id"]);

        $alert = $this->alertModel->getAlertFull($_REQUEST["id"]);

        if (!$alert) {
	    self::httpError(404);
	    die;
	}

	if (!$alert instanceof PDOException) {
            $_SESSION["sova"]["alert_to_process"] = $alert["alert_id"];
            $this->smarty->assign("alert", $alert);
        } else {
            self::logError($alert);
	    self::httpError(404);
	    die;
        }

        $this->smarty->assign("already_processed", $alert["status"] == alertModel::STATUS_ACKNOWLEDGED ||  $alert["status"] == alertModel::STATUS_CANCELED);

        if ($_POST && $alert["alert_id"]) {
            $data[":alert_id"] = $alert["alert_id"];
            $data[":notes"] = $_POST["notes"] = trim($_POST["notes"], " \n\t\r");
            $data[":type"] = $_POST["type"];

            // check notes are not empty
            if ('' != $data[":notes"] && '' != $data[":type"]) {
                if (($res = $this->alertModel->processAlert($data)) instanceof PDOException) {
                    self::sessionNotification("Виникла непередбачена ситуація. Зверніться до адміністратора системи " . $res->getMessage(), "error");
                    self::logError($res);
                } else {
                    $this->smarty->assign("success", true);
                    unset($_SESSION["sova"]["alert_to_process"]);
                }
            } else {
                self::sessionNotification("Не всі необхідні поля були заповнені", "error");
                self::redirect($_SERVER["REQUEST_URI"]);
            }

            $this->smarty->assign("data", $_POST);
        }

        $this->smarty->display("alert.tpl");
    }

}
