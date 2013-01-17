<?php
/**
 *
 */

/**
 *
 * @author ISM-UKRAINE\a.yegorov
 *
 */
class textDatatype extends stringDatatype {
	protected $PDOType = PDO::PARAM_LOB;
}