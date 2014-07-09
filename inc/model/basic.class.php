<?php

/**
 * This file contain basicModel class.
 * This class contain basic methods and functions for all models
 *
 *
 * @copyright  2010 A. Yegorov
 * @version    $Id:$
 * @author     Alexander Yegorov (boldhedgehog@gmail.com)
 *
 */
class ModelException extends Exception
{

}

class basicModel
{

    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    /**
     *
     * @var dbModel
     */
    protected $db = NULL;
    protected $dbms = NULL;
    protected $filters = array();
    protected $filtersBase = array();
    protected $joins = array();
    protected $joinsBase = array();
    protected $aliases = array();
    protected $aliasesBase = array();
    protected $tableName = null;
    protected $alias = "";
    protected $keys = array();
    protected $primaryKey = false;
    protected $indexes = array();
    protected $uniques = array();
    protected $fields = array();
    protected $data = NULL;
    protected $lastInsertId = NULL;
    protected $orders = array();
    protected $ordersBase = array();
    protected $pageSize = NULL;
    protected $currentPage = 1;

    /**
     * Constructor
     *
     * @param PDO object
     *
     */
    public function __construct($dbms = NULL)
    {
        if (!$this->tableName)
            throw new ModelException("You must override table name");

        if ($dbms == NULL) {
            if (!is_array($GLOBALS["DATABASES"]) || count($GLOBALS["DATABASES"]) == 0) {
                throw new ModelException("No databases defined?");
            }
            if ($this->dbms != NULL) {
                $this->db = $GLOBALS["DATABASES"][$this->dbms];
            } else {
                $this->db = $GLOBALS["DATABASES"][$GLOBALS["DEFAULT_DBMS"]];
            }
        } else
            $this->db = $GLOBALS["DATABASES"][$dbms];

        $this->alias = $this->tableName{0};

        return $this;
    }

    public function getId()
    {
        return ($this->data && isset($this->data[$this->primaryKey])) ? $this->data[$this->primaryKey] : NULL;
    }

    /**
     * Sets a filter.
     *
     * @param $field
     * @param $op
     * @param $value
     * 
     * @return basicModel
     */
    public function setFilter($field, $op, $value)
    {
        $expression = "$field $op " . (is_array($value) ? implode(',', $value) : $value);

        unset($this->filters[$expression]);

        $this->filters[$expression] = array('field' => $field, 'op' => $op, 'value' => $value);

        return $this;
    }

    /**
     * @return dbModel|null
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @brief Get all records.
     *
     * This function gets all records from the database according to
     * the filters, limits and sorted according to the sorting
     * options.
     *
     * @warning This function doesnt do any paging! If you want to
     *          use the paging options, use getPage() instead.
     *
     * @public
     * @see getPage()
     * @return array an array of key/value arrays representing the records.
     */
    public function getAll()
    {
        list($query, $bindings) = $this->_formatGetAllQuery();

        $all = $this->db->fetchAll($query, $bindings, true);

        return $this->_postProcess($all);
    }

    public function getCount()
    {
        list($query, $bindings) = $this->_formatGetAllQuery(true);

        $all = $this->db->fetchOne($query, $bindings, true);

        return isset($all['total_rows']) ? (int) $all['total_rows'] : null;
    }

    public function _postProcess(&$result)
    {
        //$joins = array_merge($this->joinsBase, $this->joins);

        foreach ($result as $key => $row) {
            foreach ($row as $field => $value) {
                if (preg_match('/([0-9a-z_]+)\[([0-9a-z_]+)\]/i', $field, $matches)) {
                    unset($result[$key][$field]);
                    $result[$key][$matches[1]][$matches[2]] = $value;
                }
            }
            /*foreach ($joins as $key=>$join) {
           $alias = $join['alias'];
           }*/
        }

        return $result;
    }

    /**
     *
     * @return string Query
     */
    protected function _formatGetAllQuery($count = false)
    {
        $bindings = array();
        $filterCopy = array();

        $joins = array_merge($this->joinsBase, $this->joins);
        $joinedFrom = array();
        $joinClauses = array();

        foreach ($joins as $key => $join) {
            if (!is_object($join['model'])) continue;
            $joinedFrom[] = $join['model']->_getSelectFields($join['alias'], $join['alias'], $join['fields']);
            $joinClauses[] = $this->_getJoinClause($join);
        }

        foreach ($this->filters as $key => $filter) {
            $field = $filter["field"];

            if (strpos($field, '.')) {
                list($alias, $field) = explode('.', $field, 2);
            } else {
                $alias = $this->alias;
            }

            if (is_array($filter['value']) && strtolower($filter['op']) == 'in') {
                $placeholderArray = array();
                foreach ($filter['value'] as $index => $value) {
                    $placeholder = $field . hashKey($key) . "_{$index}";
                    $bindings[":{$placeholder}"] = $value;
                    $placeholderArray[] = ":{$placeholder}";
                }
                $filterCopy[] = "`$alias`.`$field` IN (" . implode(',', $placeholderArray) . ")";
            } elseif (strtolower($filter['op']) == 'not null') {
                $filterCopy[] = "`$alias`.`$field` IS NOT NULL";
            } elseif (strtolower($filter['op']) == 'null') {
                $filterCopy[] = "`$alias`.`$field` IS NULL";
            } else {
                $placeholder = $field . hashKey($key);
                $bindings[":{$placeholder}"] = $filter["value"];
                $filterCopy[] = "`$alias`.`$field` {$filter["op"]} :{$placeholder}";
            }
        }

        if (count($filterCopy) > 0)
            $conditions = " WHERE " . implode(" AND ", $filterCopy);
        else
            $conditions = "";

        $joinedFrom = ($joinedFrom) ? ("\n," . implode("\n,", $joinedFrom)) : "";

        $query = "SELECT " . ($count ? ' count(*) AS total_rows' : $this->_getSelectFields()) . " $joinedFrom FROM `$this->tableName` AS `$this->alias`";

        if ($joinClauses) $query .= "\n" . implode("\n", $joinClauses);
        $query .= $conditions;

        if (!$count) {
            $orders = array_merge($this->ordersBase, $this->orders);

            foreach ($orders as $key => $order) {
                if (strpos('.', $key)) {
                    list($alias, $field) = explode('.', $key);
                } else {
                    $alias = $this->alias;
                    $field = $key;
                }
                $orders[$key] = "`$alias`.`$field` $order";
            }

            if ($orders) {
                $query .= ' ORDER BY ' . implode(',', $orders);
            }

            if ($this->pageSize && $this->currentPage) {
                $query .= (' LIMIT ' . ((intval($this->currentPage) - 1) * intval($this->pageSize))
                    . ',' . intval($this->pageSize));
            }
        }

        return array($query, $bindings);
    }

    protected function _getJoinClause($join)
    {
        $clause = "{$join['type']} JOIN ";
        /* @var $model basicModel */
        $model = $join['model'];
        $clause .= "`" . $model->getTableName() . "` AS `{$join['alias']}` ON ";
        $clause .= $this->_joinConditionClause($join['conditions'], $join['alias']);

        return $clause;
    }

    protected function getOne($key)
    {
        $result = $this->db->fetchOne("SELECT `{$this->alias}`.*
                                     FROM `{$this->tableName}` AS `{$this->alias}`
                                     WHERE `{$this->alias}`.`{$this->primaryKey}`=:pkey", array(':pkey' => $key), true);

        if (is_array($result) && count($result) > 0) {
            return $result;
        } else {
            return false;
        }
    }

    protected function getColumn($key, $column)
    {
        $column = preg_replace('/[^a-z0-9_-]/i', '', $column);

        $result = $this->db->fetchOne(
            "SELECT `{$this->alias}`.`{$column}`
                 FROM `{$this->tableName}` AS `{$this->alias}`
                 WHERE `{$this->alias}`.`{$this->primaryKey}`=:pkey",
            array(':pkey' => $key),
            true
        );

        if (is_array($result) && count($result) > 0) {
            return $result[$column];
        } else {
            return false;
        }
    }

    /**
     * @brief Helper function for get{Max, Min, Sum}
     *
     * @param type (MIN|MAX|SUM)
     * @param fieldName
     * @private
     *
     * @return int
     */
    protected function _getAggregate($type, $fieldName)
    {
        list($query, $bindings) = $this->_formatGetAllQuery();
        $query = preg_replace("/ORDER BY .*$/", "", $query);
        $query = preg_replace("/SELECT (.*?) FROM/", "SELECT " . $type . "(" . $this->alias . ".$fieldName) FROM", $query);
        $result = $this->db->fetchAll($query, $bindings, true);

        if (is_array($result) && count($result) > 0) {
            return $result[0];
        } else {
            return false;
        }
    }

    /**
     * @brief Gets a min value of a field
     *
     * This query returns the min() of a column with ALL the filters applied.
     * @param fieldName
     * @public
     * @see getMax()
     * @return int
     */
    function getMin($fieldName)
    {
        return $this->_getAggregate("MIN", $fieldName);
    }

    /**
     * @brief Gets a max value of a field
     *
     * This query returns the max() of a column with ALL the filters applied.
     * @param fieldName
     * @public
     * @see getMin()
     * @return int
     */
    function getMax($fieldName)
    {
        return $this->_getAggregate("MAX", $fieldName);
    }

    /**
     * @brief Gets a sum value of a column
     *
     * This query returns the sum() of a column with ALL the filters applied.
     * @param fieldName
     * @public
     * @see getMax()
     * @return int
     */
    function getSum($fieldName)
    {
        return $this->_getAggregate("SUM", $fieldName);
    }

    public function load($key)
    {
        if ($this->data) {
            $this->resetData();
        }
        if (!is_null($key)) {
            $this->data = $this->getOne($key);
        }
        return $this;
    }

    public function assureLoaded($key = NULL)
    {
        if (!$this->data || !isset($this->data[$this->primaryKey])) {
            if (is_null($key)) return false;
            $this->load($key);
        }

        if (!$this->data) {
            return false;
        }

        return true;
    }

    /**
     * Get internal data array, or its value for $key.
     *
     * @param mixed $key
     * @return mixed
     */
    public function getData($key = null)
    {
        if ($key === null) {
            return $this->data;
        }

        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function resetData()
    {
        $this->data = NULL;
        return $this;
    }

    public function resetFilters()
    {
        $this->filters = $this->filtersBase;
        return $this;
    }

    public function resetOrders()
    {
        $this->orders = $this->ordersBase;
        return $this;
    }

    public function setOrder($field, $order = self::ORDER_ASC)
    {
        $this->orders[$field] = $order;
        return $this;
    }

    public function getLastInsertId()
    {
        return ($this->lastInsertId) ? $this->lastInsertId : $this->db->lastInsertId();
    }

    public function addField(basicDatatype $field)
    {
        $this->fields[$field->getName()] = $field;
        return $this;
    }

    /**
     * Returns fields list for select query
     *
     * @param string $prefix will be used in fields list names after AS
     * @param string $alias will be used for fields list as table alias
     * @param mixed $whichfields which fields to add. NULL - for all model fields
     * @return string
     */

    protected function _getSelectFields($prefix = "", $alias = NULL, $whichfields = NULL)
    {
        $fields = array();
        if (!$alias) $alias = $this->alias;
        foreach ($this->fields as $field) {
            $name = $field->getName();
            if (is_array($whichfields) && !in_array($name, $whichfields)) {
                continue;
            }
            $fields[] = "`$alias`.`$name` AS `" . (($prefix != "") ? ($prefix . "[") : "") . $name . (($prefix != "")
                ? "]" : "") . "`";
        }

        return ($fields) ? implode(", ", $fields) : "*";
    }

    /**
     * Add join to all select queries
     *
     * @param basicModel|string $dataObject
     * @param array|bool|string $cond
     * @param bool|string $alias
     * @param array $whichfields
     * @return basicModel
     *
     * @see addJoin()
     */
    public function addLeftJoin($dataObject, $cond = false, $alias = false, $whichfields = NULL)
    {
        return $this->addJoin($dataObject, $cond, $alias, $whichfields, "LEFT");
    }

    /**
     * Add join to all select queries
     *
     * @param basicModel|string $dataObject
     * @param array|bool|string $condition
     * @param bool|string $alias
     * @param array $whichFields
     * @param string $type join type
     * @return basicModel
     */
    public function addJoin($dataObject, $condition = false, $alias = false, $whichFields = NULL, $type = "INNER")
    {
        if (is_string($dataObject))
            $dataObject = new $dataObject();
        if ($alias === false || isset($this->aliases[$alias])) {
            $i = 1;
            $dataObjectName = $dataObject->getTableName();
            do {
                $alias = substr($dataObjectName, 0, $i++);
            } while (isset($this->aliases[$alias]) && $i < strlen($dataObjectName));
            $i = 2;
            while (isset($this->aliases[$alias]))
                $alias = $dataObjectName . ($i++);
        }
        $this->aliases[$alias] = true;

        $do = clone($dataObject);
        $do->joins = array();
        $this->joins[] = array("model" => $do, "conditions" => $condition, "alias" => $alias, "fields" => $whichFields, "type" => $type);

        $index = count($this->joins) - 1;
        foreach ($dataObject->joins as $j) {
            $joinAlias = $j["alias"] = $alias . "_" . $j["alias"];
            $condition = $j["conditions"];
            // make sure that the joins of this dataobject join to the
            // joined dataobject and not to this dataobject, snap je
            if (is_string($condition)) {
                $condition = array($alias . "." . $condition => $joinAlias . "." . $condition);
            } else {
                // array
                foreach ($condition as $here => $there) {
                    $k = $here;
                    if (is_numeric($here))
                        $here = $there;

                    if (!preg_match("~^'~", $here))
                        if (preg_match("~\.~", $here))
                            $here = $alias . "_" . $here;
                        else
                            $here = $alias . "." . $here;

                    if (!preg_match("~^'~", $there))
                        if (preg_match("~\.~", $there))
                            $there = $joinAlias . "_" . $there;
                        else
                            $there = $joinAlias . "." . $there;

                    unset($condition[$k]);
                    $condition[$here] = $there;
                }
            }
            $j["conditions"] = $condition;
            $this->joins[] = $j;
            if (is_numeric($index))
                $index = array($index);
            $index[] = count($this->joins) - 1;
        }

        return $this;
    }

    public function removeJoin($index)
    {
        if (!is_array($index))
            $index = array($index);
        foreach ($index as $i)
            if (!empty($this->joins[$i])) {
                unset($this->aliases[$this->joins[$i][2]]);
                unset($this->joins[$i]);
            }
    }

    public function resetJoins()
    {
        $this->joins = $this->joinsBase;
        $this->aliases = array_merge($this->aliasesBase, array($this->alias => true));
        return $this;
    }

    protected function _joinConditionClause($cond, $alias)
    {
        if (is_string($cond) && preg_match("!^[\w_]+$!", $cond)) {
            // single field: implies corresponding keys
            $x[] = "(" . $this->alias . ".$cond = $alias.$cond)";
        } elseif (is_string($cond)) {
            $x[] = "($cond)";
        } elseif (is_array($cond)) {
            foreach ($cond as $here => $there) {
                if (is_numeric($here))
                    $here = $there;
                if ($here{0} != "'" && strpos($here, ".") === false)
                    $here = "`{$this->alias}`.`$here`";
                if ($there{0} != "'" && strpos($there, ".") === false)
                    $there = "`$alias`.`$there`";
                $x[] = "($here = $there)";
                // $x[] = "(".$this->alias.".$here = $alias.$there)";
            }
        }
        return implode(" AND ", $x);
    }

    public function __get($name)
    {
        switch ($name) {
            case "tableName":
                return $this->getTableName();

            case "fields":
                return $this->getFields();

            case "lastInsertId":
                return $this->getLastInsertId();

            default:
                $trace = debug_backtrace();
                trigger_error(
                    'Undefined property via __get(): ' . $name .
                        ' in ' . $trace[0]['file'] .
                        ' on line ' . $trace[0]['line'],
                    E_USER_ERROR);
        }
        ;
    }

    public function  __toString()
    {
        return (string)$this->tableName;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function assertNotEmpty()
    {
        foreach (func_get_args() as $arg) {
            if (empty($arg))
                throw new PDOException("Missed required argument");
        }
        return $this;
    }

}
