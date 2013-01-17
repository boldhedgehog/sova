<?php
/**
 *
 */

/**
 *
 * @author ISM-UKRAINE\a.yegorov
 *
 */
class stringDatatype extends basicDatatype {
	protected $PDOType = PDO::PARAM_STR;
	protected $length;
}