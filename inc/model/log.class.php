<?php
/**
 * This file contain logModel class.
 * This class contain method for tracking all operations in the system.
 *
 *
 * @copyright  2010 Alexander Yegorov
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
*/


/**
 *
 * @author Alexander Yegorov
 *
 */
class logModel extends basicModel {
	
	protected static $instance = NULL;
	protected $tableName = 'log';
	protected $primaryKey = "log_id";

	private function __constructor() {
	    
	}

	/**
	 * Return singletone instance
	 * @return logModel  
	 */
	public static function getInstance() {
	    if (self::$instance == NULL) {
		$class = __CLASS__;
		self::$instance = new $class();
	    }
	    return self::$instance;
	}

	public static function logAction($data) {
	    $instance = self::getInstance();

	    $stmt = $instance->db->prepare("INSERT INTO `{$instance->tableName}`
		(`timestamp`, `object`, `method`, `object_id`, `description`, `referer`, `operator_id`, `ip`, `user_agent`)
	    VALUES (:timestamp, :object, :method, :object_id, :description, :referer, :operator_id, :ip, :user_agent)");

	    $stmt->execute($data);
	}

	private function _install() {
		$stmt = $this->db->prepare("CREATE TABLE IF NOT EXISTS `log` (
  `log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` bigint(20) unsigned NOT NULL,
  `object` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Имя класса',
  `method` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Имя метода класса',
  `object_id` bigint(20) NOT NULL COMMENT 'Идентификатор ',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Примечания',
  `referer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `operator_id` bigint(20) DEFAULT NULL,
  `ip` char(40) COLLATE utf8_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;"
);
		
		$stmt->execute();
	}
}

?>