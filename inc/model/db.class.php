<?php

/**
 * This file contain db class with memcache support
 *
 * @copyright  2010
 * @version    $Id:$
 * @author
 *
*/

class dbModel extends PDO {


    private $mc;
    private $mc_lock;
    private $local_mc;
    private $mcs;
    private $gmc;

/**
* Setings
*/
    private $useMemcache = false;
    private $useLogging  = false;
    
/**
 * Destructor
 *
 */
    function __destruct() {
        if ($this->useMemcache)
          unset($this->mcs);
    }

/**
* Get rowset from DB or MEMCACHE
*
* @param string $query   | SQL query in PDO style
* @param array $bind     | array with PDO vars
* @param bool  $mc       | if true then try to use MEMCACHE to get result
* @param bool            | true - rowset, false - one row
*
* @return array | false
*/
    
    public function fetchAll($query, $bind, $mc = false, $rowset = true) {
        if ($mc && $this->useMemcache) {
            // try to use memcache
            $res = $this->selectFromCache($query, $bind, false, 240, 720);
            if (!$res) {
                return array();
            } else {
                if ($res == '### NULL ###') return array();
                return $res;
            }
        } else {
          // no memcache DIRECT QUERY
            $stmt = $this->prepare($query);
            if (is_array($bind))
              foreach ($bind as $name=>$value)
                $stmt->bindValue($name, $value);

            try {
                $stmt->execute();
            } catch (PDOException $e) {
                print_r($e);
                return false;
            }
            if ($stmt->rowCount() > 0) {
                if ($rowset) {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                    $stmt = null;
                    return $rows;
                } else {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                    $stmt = null;
                    return $row;
                }
            } else {
                $stmt->closeCursor();
                $stmt = null;
                return array();
            }
        }
    }
    
    
/**
* Get row from DB or MEMCACHE
*
* @param string $query   | SQL query in PDO style
* @param array $bind     | array with PDO vars
* @param bool  $mc       | if true then try to use MEMCACHE to get result
*
* @return array | false
*/
    
    public function fetchOne($query, $bind, $mc = false) {
        return $this->fetchAll($query, $bind, $mc = false, false);
    }

    
/**
* Execute query
*
* @param string $query   | SQL query in PDO style
* @param array $bind     | array with PDO vars
*
* @return bool
*/
    
    public function execute($query, $bind) {
        $stmt = $this->prepare($query);
        if (is_array($bind))
        foreach ($bind as $name=>$value)
            $stmt->bindValue($name, $value);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            echo $query;
            print_r($bind);
            print_r($e);
            return false;
        }
        return true;
    }
    


    private function setTxnIsoLevelRU() {
        $stmt = $this->prepare("SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
        return $stmt->execute();
    }

    private function setTxnIsoLevelRR() {
        $stmt = $this->prepare("SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ");
        return $stmt->execute();
    }

    private function getTxnIsoLevel() {
        $stmt = $this->prepare("SELECT @@tx_isolation");
        $stmt->execute();
        $res = $stmt->fetchAll();
        return $res;
    }
    
}