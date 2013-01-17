<?php

/**
 *
 */
class settingsController extends basicController
{

    /* @var operatorModel */
    protected $operatorModel = false;

    public function __construct()
    {
        $this->operatorModel = new operatorModel();

        return parent::__construct();
    }

    public function indexAction()
    {
        self::httpError(404);
    }

    public function saveAction()
    {
        $data = $_POST;

        $response = array();

        try {
            $this->getCurrentOperator();

            // from key is invalid
            if (!isset($data['form_key']) || $data['form_key'] != $this->operatorInfo['form_key']) {
                throw new Exception('Invalid from key');

            } elseif (!isset($this->operatorInfo['operator_id']) || ! $this->operatorInfo['operator_id']) {
                throw new Exception('No current logged in operator');

            } else {
                $data = $this->operatorModel->saveSettings($this->operatorInfo['operator_id'], $data);

                $_SESSION['operator']['settings'] = $data;

                $this->getCurrentOperator();

                $response = array(
                    'reload' => true,
                    //'data' => $data
                );
            }

        } catch (Exception $e) {
            $response['error']['message'] = $e->getMessage();
        }

        echo json_encode($response);
    }

}
