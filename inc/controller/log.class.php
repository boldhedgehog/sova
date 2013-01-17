<?php

/**
 *
 */
class logController extends basicController {

    public function __construct() {
        return parent::__construct();
    }

    public function indexAction() {
	self::httpError(404);
    }

}
