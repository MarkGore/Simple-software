<?php

/**
 * Description of Database
 *
 * @author mark
 */
class database
{

    public $_where = array();
    public $_TOTAL_QUERIES = 0;
    protected $_orderBy = array();
    protected $_groupBy = array();
    protected $_bindParams = array('');
    private $linkId = NULL;
    private $lastInsertId;

    public function __construct($host, $db, $username, $password)
    {
        try {
            if (!$this->connect($host, $db, $username, $password)) {
                echo 'Could not establish a connection.';
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    private function connect($host = '', $database = '', $username = '', $password = '')
    {
        if (is_null($this->linkId)) {
            try {
                $this->linkId = mysql_connect($host, $username, $password);
            } catch (Exception $e) {
                echo 'Could not establish a connection.';
            }
            // If there was an error establishing a connection, return false.
            if (!is_resource($this->linkId)) {
                return FALSE;
            }
            // If we couldn't select the database, return false.
            if (!$this->selectDb($database)) {
                return FALSE;
            } // Connection was a success.
            else {
                return TRUE;
            }
        } else {
            return;
        }
    }

    private function selectDb($database = '')
    {
        // If there was an error selecting the database, return false.
        if (!mysql_select_db($database, $this->linkId)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function orderBy($orderByField, $orderbyDirection = "DESC")
    {
        $allowedDirection = Array("ASC", "DESC");
        $orderbyDirection = strtoupper(trim($orderbyDirection));
        $orderByField = preg_replace("/[^-a-z0-9\.\(\),_]+/i", '', $orderByField);

        if (empty($orderbyDirection) || !in_array($orderbyDirection, $allowedDirection))
            die('Wrong order direction: ' . $orderbyDirection);

        $this->_orderBy[$orderByField] = $orderbyDirection;
        return $this;
    }

    public function groupBy($groupByField)
    {
        $groupByField = preg_replace("/[^-a-z0-9\.\(\),_]+/i", '', $groupByField);
        $this->_groupBy[] = $groupByField;
        return $this;
    }

    public function where($whereProp, $whereValue = null)
    {
        $this->_where[] = Array($whereValue, $whereProp);
        return $this;
    }

    public function delete($tableName, $numRows = null)
    {
        global $botwith;
        $query = "DELETE FROM " . $tableName;
        $query .= ' ' . $this->buildWhere();
        try {
            $this->queryResult = mysql_query($query, $this->linkId);
            $this->_TOTAL_QUERIES++;
        } catch (Exception $e) {
            echo $this->getError();
        }
        $this->_where = array();
        $this->_orderBy = array();
        $this->_groupBy = array();
        $botwith->cache['queries'] = $this->_TOTAL_QUERIES;
        return $this->queryResult;
    }

    public function buildWhere()
    {
        $fields = array();

        foreach ($this->_where as $test) {
            $value = $test[0];
            if (is_string($value)) {
                $value = '"' . $test[0] . '"';
            }
            $fields[] = "`{$test[1]}` = $value";
        }

        $where_sql = implode(' AND ', $fields);
        if (!empty($where_sql))
            return 'WHERE ' . $where_sql;
        return null;
    }

    public function replace($tableName, $tableData)
    {
        global $botwith;
        $query = "REPLACE INTO " . $tableName . " (";
        foreach ($tableData as $column => $value) {
            $query .= '' . $column . '' . ", ";
        }
        $query = rtrim($query, ', ');
        $query .= ")";
        $query .= " VALUES(";
        foreach ($tableData as $column => $value) {
            if (is_string($value)) {
                $query .= '"' . $value . '"' . ", ";
                continue;
            }
            if (!is_array($value)) {
                $query .= '' . $value . '' . ", ";
                continue;
            }
        }
        $query = rtrim($query, ', ');
        $query .= ")";
        $query .= ' ' . $this->buildWhere();
        try {
            $this->queryResult = mysql_query($query, $this->linkId);
            $this->_TOTAL_QUERIES++;
        } catch (Exception $e) {
            echo $this->getError();
        }
        $this->_where = array();
        $this->_orderBy = array();
        $this->_groupBy = array();
        $botwith->cache['queries'] = $this->_TOTAL_QUERIES;
        return $this->queryResult;
    }

    public function update($tableName, $tableData)
    {
        global $botwith;
        $query = "UPDATE " . $tableName . " SET ";
        foreach ($tableData as $column => $value) {
            $query .= "`" . $column . "` = ";
            if (is_string($value)) {
                $query .= '"' . $value . '"' . ", ";
                continue;
            }
            if (!is_array($value)) {
                $query .= '' . $value . '' . ", ";
                continue;
            }
            $key = key($value);
            $val = $value[$key];
            //echo $key.' '.$val.' dick';
            switch ($key) {
                case '[I]':
                    $query .= $column . $val . ", ";
                    break;
                case '[F]':
                    if (!empty ($val[1])) {
                        $query .= str_replace('?', $this->build($val[1]), $val[0] . ", ");
                    } else {
                        $query .= $val[0] . ", ";
                    }
                    break;
                case '[N]':
                    if ($val == null)
                        $query .= "!" . $column . ", ";
                    else
                        $query .= "!" . $val . ", ";
                    break;
                default:
                    die ("Wrong operation");
            }
        }
        $query = rtrim($query, ', ');
        $query .= ' ' . $this->buildWhere();
        try {
            $this->queryResult = mysql_query($query, $this->linkId);
            $this->_TOTAL_QUERIES++;
        } catch (Exception $e) {
            echo $this->getError();
        }
        $this->_where = array();
        $this->_orderBy = array();
        $this->_groupBy = array();
        $botwith->cache['queries'] = $this->_TOTAL_QUERIES;
        return $this->queryResult;
    }

    public function build($array)
    {
        $query = '';
        foreach ($array as $value) {
            if (is_string($value)) {
                $query .= '"' . $value . '"' . ", ";
                continue;
            }
            if (!is_array($value)) {
                $query .= '' . $value . '' . ", ";
                continue;
            }
        }
        $query = rtrim($query, ', ');
        return $query;
    }

    public function insert($tableName, $insertData)
    {
        global $botwith;
        $query = 'INSERT INTO ' . $tableName;
        $query .= '(`' . implode(array_keys($insertData), '`, `') . '`)';
        $query .= ' VALUES(';
        foreach ($insertData as $column => $value) {
            if (is_string($value)) {
                $query .= '"' . $value . '"' . ", ";
                continue;
            } else {
                $query .= '' . $value . '' . ", ";
                continue;
            }
        }
        $query = rtrim($query, ', ');
        $query .= ')';
        try {
            $this->queryResult = mysql_query($query, $this->linkId);
            $this->_TOTAL_QUERIES++;
        } catch (Exception $e) {
            echo $this->getError();
        }
        $this->lastInsertId = mysql_insert_id();
        $this->_where = array();
        $this->_orderBy = array();
        $this->_groupBy = array();
        $botwith->cache['queries'] = $this->_TOTAL_QUERIES;
        return $this->queryResult;
    }

    public function getOne($tableName, $columns = '*')
    {
        $res = $this->get($tableName, 1, $columns);

        if (is_object($res))
            return $res;

        if (isset($res[0]))
            return $res[0];

        return null;
    }

    public function get($tableName, $numRows = null, $columns = '*')
    {
        global $botwith;
        if (empty($columns))
            $columns = '*';
        $results = array();
        $column = is_array($columns) ? implode(', ', $columns) : $columns;
        $query = "SELECT $column FROM " . $tableName . ' ' . $this->buildWhere() . ' ' . $this->_buildOrderBy() . ' ' . $this->_buildGroupBy() . ' ' . $this->_buildLimit($numRows);
        try {
            $queryResult = mysql_query($query, $this->linkId);
            $this->_TOTAL_QUERIES++;
        } catch (Exception $e) {
            echo $this->getError();
        }
        if (!empty($queryResult))
            while ($row = mysql_fetch_object($queryResult)) {
                array_push($results, $row);
            }
        $this->_where = array();
        $this->_orderBy = array();
        $this->_groupBy = array();
        $botwith->cache['queries'] = $this->_TOTAL_QUERIES;
        return $results;
    }

    protected function _buildOrderBy()
    {
        if (empty($this->_orderBy))
            return;

        $query = " ORDER BY ";
        foreach ($this->_orderBy as $prop => $value)
            $query .= $prop . " " . $value . ", ";
        return rtrim($query, ', ') . " ";
    }

    protected function _buildGroupBy()
    {
        if (empty($this->_groupBy))
            return;
        $_query = " GROUP BY ";
        foreach ($this->_groupBy as $key => $value)
            $_query .= $value . ", ";

        return rtrim($_query, ', ') . " ";
    }

    protected function _buildLimit($numRows)
    {
        if (!isset ($numRows))
            return;
        $query = null;
        if (is_array($numRows))
            $query .= ' LIMIT ' . (int)$numRows[0] . ', ' . (int)$numRows[1];
        else
            $query .= ' LIMIT ' . (int)$numRows;
        return $query;
    }

    public function inc($num = 1)
    {
        return Array("[I]" => "+" . (int)$num);
    }

    public function dec($num = 1)
    {
        return Array("[I]" => "-" . (int)$num);
    }

    public function not($col = null)
    {
        return Array("[N]" => (string)$col);
    }

    public function func($expr, $bindParams = null)
    {
        return Array("[F]" => Array($expr, $bindParams));
    }

    public function now($diff = null, $func = "NOW()")
    {
        return Array("[F]" => Array($this->interval($diff, $func)));
    }

    public function interval($diff, $func = "NOW()")
    {
        $types = Array("s" => "second", "m" => "minute", "h" => "hour", "d" => "day", "M" => "month", "Y" => "year");
        $incr = '+';
        $items = '';
        $type = 'd';

        if ($diff && preg_match('/([+-]?) ?([0-9]+) ?([a-zA-Z]?)/', $diff, $matches)) {
            if (!empty($matches[1]))
                $incr = $matches[1];
            if (!empty($matches[2]))
                $items = $matches[2];
            if (!empty($matches[3]))
                $type = $matches[3];
            if (!in_array($type, array_keys($types)))
                trigger_error("invalid interval type in '{$diff}'");
            $func .= " " . $incr . " interval " . $items . " " . $types[$type] . " ";
        }
        return $func;
    }
}
