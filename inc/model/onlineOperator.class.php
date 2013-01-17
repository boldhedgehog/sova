<?php

/**
 * This file contain onlineOperatorsModel class.
 * This class contain method for operating livehelp sessions related data in DB.
 *
 *
 * @copyright  2008 ADOTUBE.com
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */
class onlineOperatorModel extends basicModel
{
    const OPERATOR_STATUS_FREE = 0;
    const OPERATOR_STATUS_BUSY = 1;
    /* TTL in seconds */
    const OPERATOR_SESSION_TTL = 600;

    protected $tableName = 'online_operator';
    protected $primaryKey = "online_operator_id";

    public function login($operator_id, $timestamp)
    {
        $stmt = $this->db->prepare("INSERT INTO `{$this->tableName}`
			(`operator_id`, `loggedin`)
			VALUES (:OperatorId, :LoggedIn)
			ON DUPLICATE KEY UPDATE `lastvisit`=:LoggedIn");

        try {
            $stmt->execute(array(":OperatorId" => $operator_id, ":LoggedIn" => $timestamp));
        } catch (PDOException $e) {
            return $e;
        }
        return $this;
    }

    public function logout($operator_id)
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->tableName}` WHERE `operator_id`=:OperatorId");

        try {
            $stmt->execute(array(':OperatorId' => $operator_id));
        } catch (PDOException $e) {
            return $e;
        }
        return $this;
    }

    /**
     * Update last visit of operator or create a new record
     *
     * @param string $operator_id
     * @param integer $timestamp
     *
     * @return onlineOperatorModel|PDOException
     */
    public function refresh($operator_id, $timestamp)
    {
        $stmt = $this->db->prepare("INSERT INTO `{$this->tableName}` (`operator_id`, `loggedin`) VALUES (:OperatorId, :LoggedIn)
            ON DUPLICATE KEY UPDATE `lastvisit`=:LoggedIn");
        try {
            $stmt->execute(array(':OperatorId' => $operator_id, ':LoggedIn' => $timestamp));
        } catch (PDOException $e) {
            return $e;
        }
        return $this;
    }

    /**
     * Delete expired operator sessions.
     *
     * @param integer $timestamp Current UNIX time
     *
     * @return onlineOperatorModel|PDOException
     */
    public function dropExpired($timestamp = NULL)
    {
        if ($timestamp === NULL) {
            $timestamp = time();
        }
        $stmt = $this->db->prepare("DELETE FROM `{$this->tableName}`
            WHERE `lastvisit` < :timestamp");
        try {
            $stmt->execute(array(':timestamp' => $timestamp - self::OPERATOR_SESSION_TTL * 1000));
        } catch (PDOException $e) {
            return $e;
        }
        return $this;
    }

    /**
     * Set operator status
     * @param string $operator_id
     * @param timestamp $timestamp
     * @param int status
     */
    protected function setStatus($operator_id, $timestamp, $status)
    {
        $stmt = $this->db->prepare("UPDATE `{$this->tableName}` SET `lastvisit`=:LastVisit, `status`=:Status WHERE `operator_id`=:OperatorId");

        try {
            $stmt->execute(array(':OperatorId' => $operator_id, ':LastVisit' => $timestamp, ':Status' => $status));
        } catch (PDOException $e) {
            return $e;
        }
        return $this;
    }

    public function release($operator_id, $timestamp)
    {
        return $this->setStatus($operator_id, $timestamp, self::OPERATOR_STATUS_FREE);
    }

    public function bind($operator_id, $timestamp)
    {
        return $this->setStatus($operator_id, $timestamp, self::OPERATOR_STATUS_BUSY);
    }

    public function getLastVisit($operator_id)
    {
        if (empty($operator_id) || $operator_id < 1)
            return false;
        $result = $this->db->fetchOne(
            "LOCK TABLES `{$this->tableName}` READ;
			SELECT `lastvisit` FROM `{$this->tableName}`
			WHERE `operator_id` = :OperatorId;
			UNLOCK TABLES", array(':OperatorId' => $operator_id), true);

        if (is_array($result) && count($result) > 0) {
            return intval($result['lastvisit']);
        } else {
            return false;
        }
    }

    /**
     *
     * @return mixed - Array with data or false
     */
    public function getStatus($id)
    {
        $result = $this->db->fetchOne(
            "SELECT status FROM `{$this->tableName}` AS `{$this->alias}`
			WHERE `{$this->alias}`.`operator_id` = :operator_id", array(":operator_id" => $id), true);

        if (is_array($result) && count($result) > 0) {
            return $result["status"];
        } else {
            return false;
        }
    }

    /**
     *
     * @param bool $freeOnly
     * @return mixed - Array with data or false
     */
    public function getOnlineOperators($freeOnly = true)
    {
        if ($freeOnly) {
            $condition =  "WHERE `{$this->alias}`.`status` = ?";
            $bind = array(self::OPERATOR_STATUS_FREE);
        } else {
            $condition = '';
            $bind = array();
        }

        $result = $this->db->fetchAll(
            'SELECT TIME(FROM_UNIXTIME(loggedin)) AS loggedin_time, l.* FROM `{$this->tableName}` AS `{$this->alias}`'
			. $condition
			. 'ORDER BY `{$this->alias}`.`lastvisit` DESC', $bind, true);

        if (is_array($result) && count($result) > 0) {
            return $result;
        } else {
            return false;
        }
    }

    public function getFreeOperators()
    {
        return $this->getOnlineOperators(true);
    }

    private function _install()
    {
        $stmt = $this->db->prepare(
            'CREATE TABLE IF NOT EXISTS `online_operator` (
              `online_operator_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `operator_id` BIGINT(20)  NOT NULL,
              `status`TINYINT(3)  NOT NULL DEFAULT 0,
              `loggedin` BIGINT UNSIGNED NOT NULL,
              `lastvisit` BIGINT UNSIGNED DEFAULT NULL,
              PRIMARY KEY (`online_operator_id`),
              UNIQUE `operator_id`(`operator_id`),
              INDEX `status`(`status`)
            )
            ENGINE = MEMORY
            AUTO_INCREMENT = 1'
        );

        $stmt->execute();
    }

}