<?php
/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 * @date 2015-07-26
 * @version 2.0.1 beta
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
    private function fixTableName($table)
    {
        $res = preg_replace('/\{(?=\w+\})/', $this->_conf['prefix'], $table);
        return str_replace('}', '', $res);
    }

    /**
     * Simple query executor, expects results and returns them as an assoc array
     * @param $query
     * @param array $params
     * @return array
     */
    public function executeRawQuery($query, array $params = array())
    {
        $this->result = $this->_adapter->query($this->fixTableName($query), $params);
        return $this->result->toArray();
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
            $statement = $this->_adapter->createStatement($this->fixTableName($query));
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
    public function fetchAll($table, array $conditions, $columns = '*')
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
        }
        return $this->executeRawQuery($query, $vals);
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
            $data = $prof['parameters']->getNamedArray();
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
        return $this->executeRawQuery($query);
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
}