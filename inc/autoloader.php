<?php

class MissingException extends Exception {
	const UNABLE_TO_LOAD_MSG = "Unable to load class %s";
	
	public function __construct($message, $code = false){
		return parent::__construct(sprintf(self::UNABLE_TO_LOAD_MSG, $message), $code);
	}
}

spl_autoload_register(function ($class) {
    $filename = NULL;
    
    if (preg_match('/^([A-z0-9]+)Model$/', $class, $m)) {
            $filename = INC_PATH . "model/{$m[1]}.class.php";
    } elseif (preg_match('/^([A-z0-9]+)Controller$/', $class, $m)) {
            $filename = INC_PATH . "controller/{$m[1]}.class.php";
    } elseif (preg_match('/^([A-z0-9]+)Datatype$/', $class, $m)) {
            $filename = INC_PATH . "datatype/{$m[1]}.class.php";
    }

    if (NULL!== $filename && file_exists($filename)) {
        require_once($filename);
    }
});
