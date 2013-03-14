<?php
/**
 * This file contain hostController class.
 * This class can handle host login
 *
 *
 *
 * @copyright  2010
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */

class hostController extends watcherController
{
    /* @var $hostModel hostModel */
    protected $hostModel = false;

    protected $_id = null;

    protected static $logFilterCookieName = 'logfilterhostLog';

    /**
     * Constructor. Initializes the object
     */
    public function __construct()
    {
        $this->hostModel = new hostModel();

        return parent::__construct();
    }

    public function getRefreshUri()
    {
        return 'host/refreshStatuses/id/' . $this->_id;
    }

    public function preDispatch()
    {
        if (!$this->_id) $this->_id = self::getRequestVar('id');

        return parent::preDispatch();
    }

    public function indexAction()
    {
        parent::indexAction();

        // try to get host from DB
        /* @var $host hostModel */
        $host = (is_numeric($this->_id)) ? $this->hostModel->load($this->_id) : $this->hostModel->loadByNagiosName($this->_id);

        if (!$host->getId()) {
            self::httpError(404);
            die;
        }

        $filter = $this->_setLogFilterDefaults(self::jsonDecode(self::getCookie(self::$logFilterCookieName), true));

        $host = $host->loadContacts()
            ->loadChannels()
            ->loadZones()
            ->loadServices()
            ->loadCommunicationDevices()
            ->loadNotificationDevices()
            ->loadNagiosData()
            ->loadNagiosLog($filter)
            ->getData();

        /** @var $host array */

        $this->smarty->assignByRef('host', $host);

        $this->smarty->assign('isAjax', false);
        
        $this->smarty->assign('fromTime', time());
        $this->smarty->assign('pageTitle', "{$host['alias']}");

        $content = $this->smarty->fetch('host.tpl');

        $this->smarty->assignByRef('content', $content);
        $this->smarty->display('index.tpl');
    }

    public function passportAction()
    {
        // try to get host from DB
        /* @var $host hostModel */
        $host = (is_numeric($this->_id)) ? $this->hostModel->load($this->_id) : $this->hostModel->loadByNagiosName($this->_id);

        if (!$host->getId()) {
            self::httpError(404);
            die;
        }

        $host = $host->getData();

        if ($host['passport'] != null) {
            echo gzinflate($host['passport']);
            return;
        } else {
            self::httpError(404);
            die;
        }
    }
    
    /*public function viewlogAction() {
	$this->hostModel->viewlog();
    }*/

    public function xajaxGetService($id)
    {
        $service = new serviceController();
        return $service->xajaxGetService($id);
    }

    protected function _xajaxUpdateServiceTable($useLastcheck)
    {
        return;
    }

    protected function _updateServiceTable($useLastcheck)
    {
        $this->jsonResponse->services = array();
        if (!is_numeric($this->_id)) {
            foreach ($this->services as $key => $service) {
                if ($service['host_name'] != $this->_id) {
                        unset($this->services[$key]);
                    }
            }
        }
    }

    public function xajaxRefreshStatuses($useLastcheck = true)
    {
        parent::xajaxRefreshStatuses($useLastcheck);

        // for quick and no service states were changed
        if ($useLastcheck && !$this->services) {
            return $this->objResponse;
        }

        // try to get host from DB
        /* @var $host hostModel */
        $host = (is_numeric($this->_id)) ? $this->hostModel->load($this->_id) : $this->hostModel->loadByNagiosName($this->_id);

        if ($host->getId()) {
            $this->smarty->assign('host', $host->loadContacts()->loadServices()->loadNagiosData()->getData());
            
            $this->smarty->assign('isAjax', true);

            $html = $this->smarty->fetch('inc/services_nagvis.tpl');
            //$this->objResponse->script("$('div#nagvisServiceIconOverlay').replaceWith('$html')");
            //$this->objResponse->script("alert(\"$html\")");

            // update nagvis services
            $this->objResponse->assign('nagvisServiceIconOverlay', 'innerHTML', $html);
            $this->objResponse->script("$('img.host-map').each(function(){ scaleNagvisServiceIcons($(this)) }); initNagvisMap();");

            // update services table
            $html = $this->smarty->fetch('inc/services_table.tpl');
            $this->objResponse->assign('services-container', 'innerHTML', $html);
            //$this->objResponse->script("$('#services-container table tbody').html('" . self::jsEscapeString($html) . "')");
            $this->objResponse->script('initServiceLinks()');
            $this->objResponse->script('initTableFilter("table.services")');
            $this->objResponse->script('applyFilters("table.services")');
        }

        return $this->objResponse;
    }

    public function refreshStatusesAction()
    {
        parent::refreshStatusesAction();

        // for quick and no service states were changed
        if ($this->_useLastCheck && !$this->services) {
            return $this->jsonResponse;
        }

        // TODO: replace only if this host's services were changed.
        // update services map overlay

        // try to get host from DB
        /* @var $host hostModel */
        $host = (is_numeric($this->_id)) ? $this->hostModel->load($this->_id) : $this->hostModel->loadByNagiosName($this->_id);

        if ($host->getId()) {

            $this->smarty->assign('host', $host->loadContacts()->loadServices()->loadNagiosData()->getData());

            $this->smarty->assign('isAjax', true);

            $html = $this->smarty->fetch('inc/services_nagvis.tpl');
            //$this->objResponse->script("$('div#nagvisServiceIconOverlay').replaceWith('$html')");
            //$this->objResponse->script("alert(\"$html\")");

            // update nagvis services
            $this->jsonResponse->nagvisServiceIconOverlay = $html;

            // update services table
            $html = $this->smarty->fetch('inc/services_table.tpl');
            $this->jsonResponse->servicesContainer = $html;

            // TODO: move to JS
            /*$this->jsonResponse->script('initServiceLinks()');
            $this->jsonResponse->script('initTableFilter("table.services")');
            $this->jsonResponse->script('applyFilters("table.services")');*/
        }

        return $this->jsonResponse;
    }

    /**
     * @param $filter
     * @return xajaxResponse
     *
     * @deprecated Use getLogRowsAction with jQuery AJAX
     */
    public function xajaxGetLogRows($filter)
    {
        $filter = $this->_setLogFilterDefaults($filter);

        $this->objResponse = new xajaxResponse();

        // try to get host from DB
        /* @var $host hostModel */
        $host = (is_numeric($this->_id)) ? $this->hostModel->load($this->_id) : $this->hostModel->loadByNagiosName($this->_id);

        if ($host->getId()) {
            $this->smarty->assign('nagiosObject', $host->loadNagiosLog($filter)->getData());

            $html = $this->smarty->fetch('inc/log.tpl');
            $this->objResponse->script("$('table.log tbody').html('" . self::jsEscapeString($html) . "')");
        }

        return $this->objResponse;
    }

    public function getLogRowsAction()
    {
        $filter = $this->_setLogFilterDefaults($_POST);

        // try to get host from DB
        /* @var $host hostModel */
        $host = (is_numeric($this->_id)) ? $this->hostModel->load($this->_id) : $this->hostModel->loadByNagiosName($this->_id);

        if ($host->getId()) {
            $this->smarty->assign('nagiosObject', $host->loadNagiosLog($filter)->getData());

            $html = $this->smarty->fetch('inc/log.tpl');

            echo json_encode(
                array(
                    'response' => $html
                )
            );

            //$this->objResponse->script("$('table.log tbody').html('" . self::jsEscapeString($html) . "')");
        } else {
            self::httpError(404);
            die;
        }
    }

    public function importAction()
    {
        $data = $this->livestatusModel->getHostsFull();
        if (!$data)
            throw new mklivestatusException("No hosts found");

        $result = $this->hostModel->importFromNagios($data);
        if ($result instanceof PDOException) {
            self::logError($result);
        }

        echo "done";
    }

    protected function _setLogFilterDefaults($filter = array()) {
        if (!isset($filter['time']) || empty($filter['time'])) {
            self::logError('set time');
            $filter['time'] = mktime(0, 0, 0) - 86400;
            $this->setCookie(self::$logFilterCookieName, self::jsonEncode($filter, JSON_FORCE_OBJECT));
        }

        if (!isset($filter['time_end']) || empty($filter['time_end'])) {
            $filter['time_end'] = time();
        }

        return $filter;
    }

}
