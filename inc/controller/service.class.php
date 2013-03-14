<?php

/**
 * This file contain serviceController class.
 * This class can handle service login
 *
 *
 *
 * @copyright  2010-2012
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */
class serviceController extends watcherController
{

    /* @var serviceModel */
    protected $serviceModel = false;

    /* @var array */
    protected $serviceInfo = false;

    protected $_id = null;

    protected $_isAjax = false;

    public function __construct()
    {
        $this->serviceModel = new serviceModel();
        $this->livestatusModel = new mklivestatusModel();

        return parent::__construct();
    }

    public function preDispatch()
    {
        if (!$this->_id) $this->_id = self::getRequestVar('id');

        return parent::preDispatch();
    }

    public function getRefreshUri()
    {
        return 'service/refreshStatuses/id/' . $this->_id;
    }

    public function indexAction()
    {
        parent::indexAction();

        // try to get host from DB
        /* @var $service serviceModel */
        $service = (is_numeric($this->_id)) ? $this->serviceModel->load($this->_id) : $this->serviceModel->loadByHostIdAndNagiosName($this->_id);

        if (!$service->getId()) {
            self::httpError(404);
            die;
        }

        $data = $service->loadCards()->loadHost()->loadNagiosData()->loadNagiosLog()->getData();

        if (!self::isMobile()) {
            $data['daily_chart'] = $service->getDailyStateData();
        }

        $data['name'] = $service->getName();

        $this->smarty->assign('service', $data);

        if ($this->_isAjax) {
            $this->smarty->assign('isAjax', true);
            $this->smarty->display('service.tpl');
        } else {
            $this->smarty->assign('host', $data['host']);

            $this->smarty->assign('fromTime', time());
            $this->smarty->assign('pageTitle', $data['name'] . ' :: ' . $data['host']['name']);

            $content = $this->smarty->fetch('service.tpl');

            $this->smarty->assign('content', $content);

            $this->smarty->display('index.tpl');
        }
    }

    public function ajaxAction()
    {
        $this->_isAjax = true;

        $this->indexAction();
    }

    public function xajaxRefreshStatuses($useLastcheck = true)
    {
        $time = time();
        $this->objResponse = new xajaxResponse();

        return $this->objResponse;
    }

    protected function _updateServiceTable($useLastcheck)
    {
        $this->jsonResponse->services = array();
    }

    public function xajaxGetService($id)
    {
        $this->_id = $id;
        $this->objResponse = new xajaxResponse();
        // try to get host from DB
        /* @var $service serviceModel */
        $service = (is_numeric($this->_id)) ? $this->serviceModel->load($this->_id) : $this->serviceModel->loadByHostIdAndNagiosName($this->_id);

        if (!$service->getId()) {
            self::httpError(404);
            die;
        }

        $this->smarty->assign('isAjax', true);

        $this->smarty->assign('service', $service->loadCards()->loadHost()->loadNagiosData()->loadNagiosLog()->getData());

        $html = $this->smarty->fetch('service.tpl');

        $this->objResponse->assign('serviceDetailsContainer', 'innerHTML', $html);
        $this->objResponse->script('serviceId = \'' . $service->getId() . '\'; initServiceTabs()');

        return $this->objResponse;
    }

    public function xajaxGetLogRows($dayOffset = 0, $append = false)
    {
        $this->objResponse = new xajaxResponse();

        // try to get service from DB
        /* @var $service serviceModel */
        $service = (is_numeric($this->_id)) ? $this->serviceModel->load($this->_id) : $this->serviceModel->loadByHostIdAndNagiosName($this->_id);

        if ($service->getId()) {
            $dayOffset = intval($dayOffset);

            $time = mktime(0, 0, 0) + $dayOffset * 86400;

            $this->smarty->assign('nagiosObject', $service->loadHost()->loadNagiosLog(
                    array(
                        "time"     => array("field" => "time", "op" => ">=", "value" => $time),
                        "time_end" => array("field" => "time_end", "op" => "<", "value" => $time + 86400)
                    )
                )
                    ->getData()
            );

            $this->smarty->assign('fromTime', $time);
            $this->smarty->assign('append', $append);

            $html = $this->smarty->fetch('inc/log.tpl');

            if ($append) {
                $this->objResponse->script("$('table.log tbody').append('" . self::jsEscapeString($html) . "')");
            } else {
                $this->objResponse->script("$('table.log tbody').html('" . self::jsEscapeString($html) . "')");
            }
            $this->objResponse->script("logDayOffset = '" . --$dayOffset . "'");
        }

        return $this->objResponse;
    }

    public function importAction()
    {
        $data = $this->livestatusModel->getServices();
        if (!$data)
            throw new mklivestatusException("No services found");

        $result = $this->serviceModel->importFromNagios($data);
        if ($result instanceof PDOException) {
            self::logError($result);
        }

        echo "done";
    }

}