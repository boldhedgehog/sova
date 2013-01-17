<?php
/**
 *
 */

class DatatypeException extends Exception {
	
}

/**
 *
 * @author ISM-UKRAINE\a.yegorov
 *
 */
class basicDatatype {
	protected $name = NULL;
	protected $PDOType = PDO::PARAM_STR;
	protected $caption = NULL;
	protected $defaultValue = NULL;

	/**
	 * @var basicModel
	 */
	protected $model = NULL;
	
	public function __construct(basicModel $model) {
		if (is_null($this->name)) throw new DatatypeException("You must override name property.");
		if (is_null($this->caption)) $this->caption = ucfirst($this->name);
		$this->model = $model;
	}
	
	public function getName() {
		return $this->name;
	}
}