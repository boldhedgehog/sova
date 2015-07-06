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
            $this->smarty->clearAllCache();
            //$data['daily_chart'] = $service->getDailyStateData();
            $fromTime = self::getRequestVar('duration_period', strtotime("midnight", time()));
            $this->smarty->assign('duration_periods', $this->_getChartPeriods());
            $data['duration_period'] = $fromTime;
            $data['duration_chart'] = $service->getStateTimelineData(null, $fromTime);
            $data['duration_chart_states'] = $service->getStatesForPeriod(null, $fromTime);
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

    public function timelineAction()
    {
        // try to get host from DB
        /* @var $service serviceModel */
        $service = (is_numeric($this->_id)) ? $this->serviceModel->load($this->_id) : $this->serviceModel->loadByHostIdAndNagiosName($this->_id);

        if (!$service->getId()) {
            self::httpError(404);
            die;
        }

        $fromTime = self::getRequestVar('duration_period', strtotime("midnight", time()));
        $rows = $service->getStateTimelineData(null, $fromTime);

        header('Content-Type: application/json');
        $this->jsonResponse = new stdClass();

        $this->jsonResponse->data = $rows;
        $this->jsonResponse->states = $service->getStatesForPeriod(null, $fromTime);

        return $this->jsonResponse;
    }

    public function ajaxAction()
    {
        $this->_isAjax = true;

        $this->indexAction();
    }

    protected function _updateServiceTable($useLastcheck)
    {
        $this->jsonResponse->services = array();
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

    /**
     * @return array
     */
    protected function _getChartPeriods()
    {
        return array(
            'Година' => time() - 3600,
            '6 годин' => time() - 6 * 3600,
            '24 години' => time() - 24 * 3600,
            'Початок доби' => strtotime("midnight", time()),
            'Тиждень' => strtotime("1 week ago", time()),
            'Місяць' => strtotime("1 month ago", time()),
            '3 Місяці' => strtotime("3 months ago", time()),
            '6 Місяців' => strtotime("6 months ago", time()),
            '12 Місяців' => strtotime("12 months ago", time()),
        );
    }

}