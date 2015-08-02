<?php
/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 * @date 2015-07-26
 * @version 2.0.1 beta
 *
 * Official repo: https://github.com/ArchangelDesign/ArchangelDB2
 */

namespace ArchangelDB;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

/**
 * Class ADB2
 * @package ArchangelDB
 */
class ADB2 implements ADB2Interface
{
    /**
     * @var array|mixed
     */
    private $_conf;
    /**
     * @var Adapter
     */
    private $_adapter;
    /**
     * @var \Zend\Db\Adapter\Driver\DriverInterface
     */
    private $_driver;
    /**
     * @var \Zend\Db\ResultSet\ResultSetInterface
     */
    public $result;

    /**
     * @param null $config
     * @throws \Zend\Db\Exception\ErrorException
     */
    public function __construct($config = null)
    {
        $this->_conf = ConfigLoader::getConfig($config);
        $this->_adapter = new Adapter($this->_conf);
        $this->_driver = $this->_adapter->getDriver();
    }

    /**
     * @param $table
     * @return mixed
     */
    private function fixTableNames($table)
    {
        $res = preg_replace('/\{(?=\w+\})/', $this->_conf['prefix'], $table);
        return str_replace('}', '', $res);
    }

    private function isDropAllowed()
    {
        if (isset($this->_conf['allow_drop'])) {
            if ($this->_conf['allow_drop'] === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Simple query executor, expects results and returns them as an assoc array
     * @param $query
     * @param array $params
     * @return array
     */
    public function executeRawQuery($query, array $params = array())
    {
        $this->result = $this->_adapter->query($this->fixTableNames($query), $params);
        return $this->result;
    }

    /**
     * Executes query with prepared statement
     * @param $query
     * @param null $params
     * @return array|bool|int
     */
    public function executePreparedQuery($query, $params = null)
    {
        try {
            $statement = $this->_adapter->createStatement($this->fixTableNames($query));
            $res = $statement->execute($params);

        } catch (\Exception $e) {
            return false;
        }
        $this->result = $res;
        if ($res->isQueryResult()) {
            $res->rewind();
            $returnArray = [];
            while($res->valid()) {
                $returnArray[] = $res->current();
                $res->next();
            }
        } else {
            $returnArray = $res->getAffectedRows();
        }
        return $returnArray;
    }

    /**
     * Returns all rows with selected columns and by given criteria
     * @param $table
     * @param array $conditions column => value
     * @param string $columns
     * @return array
     */
    public function fetchAll($table, array $conditions = [], $columns = '*')
    {
        $table = $this->_conf['prefix'] . $table;
        $query = "select $columns from $table ";
        if (!empty($conditions)) {
            $query .= "where ";
            $cond = [];
            $vals = [];
            foreach ($conditions as $key => $condition) {
                $cond[] = "$key = ?";
                $vals[] = $condition;
            }
            $query .= implode(' and ', $cond);
        } else { $vals = [];}
        return $this->executeRawQuery($query, $vals)->toArray();
    }

    /**
     * Fetches single record from database. If more records fit
     * only first one is returned
     * @param $table
     * @param array $conditions
     * @param string $columns
     * @return mixed
     */
    public function fetchOne($table, array $conditions = [], $columns = '*')
    {
        $result = $this->fetchAll($table, $conditions, $columns);
        return array_shift($result);
    }

    /**
     * Returns last query string with given parameters
     * @return array|string
     */
    public function getLastQuery()
    {
        if ($this->_conf['profiler']) {
            $prof = $this->_adapter->getProfiler()->getLastProfile();
            $query = $prof['sql'];
            if (!empty($prof['parameters'])) {
                $data = $prof['parameters']->getNamedArray();
            } else {
                $data = [];
            }
            foreach ($data as $parameter) {
                $query = preg_replace('/\?/', "'$parameter'", $query, 1);
            }
            return $query;
        } else {
            return [];
        }
    }

    /**
     * Returns time last query needed to return buffered result
     * @return int
     */
    public function getLastQueryTime()
    {
        if ($this->_conf['profiler']) {
            return $this->_adapter->getProfiler()->getLastProfile()['elapse'];
        } else {
            return 0;
        }
    }

    /**
     * For backward compatibility
     * @param $query
     * @return array
     * @deprecated
     */
    public function getAll($query)
    {
        return $this->executeRawQuery($query)->toArray();
    }

    /**
     * For backward compatibility
     * @param $query
     * @param array $params
     * @return mixed
     */
    public function getFirstRow($query, array $params = [])
    {
        return $this->fetchOne($query, $params);
    }

    /**
     * For backward compatibility
     * @param $query
     * @return array|bool|int
     * @deprecated
     */
    public function query($query)
    {
        return $this->executePreparedQuery($query);
    }

    /**
     * Returns ID of last inserted record or false if no result available
     * @return int|bool
     */
    public function lastInsertId()
    {
        if (!is_object($this->result)) {
            return false;
        }
        if (!method_exists($this->result, 'getGeneratedValue')) {
            return false;
        }
        return $this->result->getGeneratedValue();
    }

    /**
     * Inserts single record to given table
     * @param $table
     * @param $record
     * @return array
     */
    public function insert($table, $record)
    {
        $columns = [];
        $values = [];
        $params = [];
        $table = "{".$table."}";
        foreach ($record as $col => $val) {
            $columns[] = $col;
            $values[] = $val;
            $params[] = '?';
        }
        $columns = implode(',', $columns);
        $params = implode(',', $params);
        $query = "insert into $table($columns) values($params)";
        return $this->executeRawQuery($query, $values);
    }

    /**
     * Deleted one or more records from given table
     * @param $table
     * @param array $conditions
     * @return int amout of deleted records
     */
    public function deleteRocords($table, array $conditions)
    {
        $values = [];
        $params = [];
        $table = "{".$table."}";
        foreach ($conditions as $col => $val) {
            $values[] = $val;
            $params[] = $col . ' = ?';
        }
        $params = implode(' and ', $params);
        $query = "delete from $table where $params";
        $this->executeRawQuery($query, $values);
        return $this->result->getAffectedRows();
    }

    /**
     * Deletes single record. If more than one record fits conditions
     * method returns false and no records are deleted
     * @param $table
     * @param array $conditions
     * @return array|bool
     */
    public function deleteSingleRocord($table, array $conditions)
    {
        $values = [];
        $params = [];
        $table = "{".$table."}";
        foreach ($conditions as $col => $val) {
            $values[] = $val;
            $params[] = $col . ' = ?';
        }
        $params = implode(' and ', $params);
        $query = "delete from $table where $params";
        $buffer = $this->fetchAll($table, $conditions, '*');
        if (!empty($buffer)) {
            return false;
        }
        return $this->executeRawQuery($query, $values);
    }

    /**
     * Removes table from database
     * @param $table
     * @return array
     */
    public function dropTable($table)
    {
        if ($this->isDropAllowed()) {
            return $this->executeRawQuery('drop table {' . $table . '}');
        } else {
            return false;
        }
    }

    public function insertTable($tableName, $columns, $keys)
    {
        // @todo: implementetion
        return true;
    }

    /**
     * Checks id table exists
     * @param $tableName
     * @return bool
     */
    public function tableExists($tableName)
    {
        try {
            $this->executeRawQuery('select 1 from {'.$tableName.'}');
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Checks if column exists
     * @param $tableName
     * @param $columnName
     * @return bool
     */
    public function columnExists($tableName, $columnName)
    {
        $tn = '{'.$tableName.'}';
        $res = $this->executeRawQuery("show columns from $tn like '$columnName'");
        if ($res->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Initializes SQL transaction
     */
    public function beginTransaction()
    {
        $this->_driver->getConnection()->beginTransaction();
    }

    /**
     * Forces commit of pending transaction
     */
    public function commitTransaction()
    {
        $this->_driver->getConnection()->commit();
    }

    /**
     * Undoes results of current transaction
     */
    public function rollbackTransaction()
    {
        $this->_driver->getConnection()->rollback();
    }
}