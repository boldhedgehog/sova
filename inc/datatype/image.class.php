<?php
/**
 *
 */

/**
 *
 * @author ISM-UKRAINE\a.yegorov
 *
 */
class imageDatatype extends stringDatatype {
	protected $PDOType = PDO::PARAM_STR;
	protected $length = 255;
}