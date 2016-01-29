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
use Zend\Db\Exception\ErrorException;
use Zend\Db\Exception\InvalidArgumentException;
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
     * @throws \Exception
     */
    public function __construct($config = null)
    {
        $this->_conf = ConfigLoader::getConfig($config);
        if (!isset($this->_conf['error-log-file'])
            || empty($this->_conf['error-log-file'])) {
            throw new \Exception(
                "Error log file needs to be set. Check your config file."
            );
        }
        ini_set("error_log", $this->_conf['error-log-file']);
        if (!isset($this->_conf['prefix'])) {
            $this->_conf['prefix'] = '';
        }
        if (!isset($this->_conf['options']['buffer_results'])) {
            $this->_conf['options']['buffer_results'] = true;
        }
        if (!$this->_conf['options']['buffer_results']) {
            $this->_conf['options']['buffer_results'] = true;
        }
        $this->_adapter = new Adapter($this->_conf);
        $this->_driver = $this->_adapter->getDriver();
        $this->checkAndDeploy();
    }

    /**
     * @param       $table
     * @param array $conditions
     * @param       $columns
     *
     * @return string
     */
    private function createCacheKey($table, array $conditions, $columns)
    {
        $input = $table . json_encode($conditions) . $columns;
        return $table . '-' . md5($input);
    }

    /**
     * @param null $hash
     *
     * @return string
     * @throws ErrorException
     */
    private function getCacheDir($hash = null)
    {
        if (!isset($this->_conf['cache-dir']) || empty($this->_conf['cache-dir'])) {
            throw new ErrorException("Cache directory is not set.");
        }
        $dir = $this->_conf['cache-dir'];
        if ($hash === null) {
            return $dir;
        }
        return $dir . '/' . $hash;
    }

    /**
     * @param string $hash
     *
     * @return bool|string
     */
    private function cacheExists($hash)
    {
        $dir = $this->_conf['cache-dir'];
        $fname = $dir . '/' . $hash;
        if (file_exists($fname)) {
            $time = filemtime($fname);
            return $fname;
        } else {
            return false;
        }
    }

    /**
     * @param       $table
     * @param array $conditions
     * @param       $columns
     *
     * @return array|null
     */
    private function fetchFromCache($table, array $conditions, $columns)
    {
        if (isset($this->_conf['enable-cache']) && $this->_conf['enable-cache']) {
            $hash = $this->createCacheKey($table, $conditions, $columns);
            if (!$path = $this->cacheExists($hash)) {
                return null;
            }
            $fsize = filesize($path);
            $handle = fopen($path, "r");
            $data = fread($handle, $fsize);
            fclose($handle);
            return json_decode($data, true);
        } else {
            return null;
        }
    }

    /**
     * @param       $table
     * @param array $conditions
     * @param       $columns
     * @param array $data
     *
     * @return null|string
     * @throws ErrorException
     */
    private function storeInCache($table, array $conditions, $columns, array $data)
    {
        if (isset($this->_conf['enable-cache']) && $this->_conf['enable-cache']) {
            $hash = $this->createCacheKey($table, $conditions, $columns);
            if ($path = $this->cacheExists($hash)) {
                unlink($path);
            }
            $path = $this->getCacheDir($hash);
            $file = fopen($path, "w+");
            $d = json_encode($data);
            fwrite($file, $d, strlen($d));
            fclose($file);
            return $d;
        } else {
            return null;
        }
    }

    /**
     * @param       $table
     * @param array $conditions
     * @param       $column
     *
     * @return bool
     */
    private function clearCachedResult($table, array $conditions, $column)
    {
        if (isset($this->_conf['enable-cache']) && $this->_conf['enable-cache']) {
            $hash = $this->createCacheKey($table, $conditions, $column);
            if ($path = $this->cacheExists($hash)) {
                return unlink($path);
            }
        } else {
            return true;
        }
    }

    /**
     * @param $table
     *
     * @return array
     * @throws ErrorException
     */
    private function clearTableCache($table)
    {
        if (!$this->getConfigValue('enable-cache')) {
            return;
        }
        $dir = $this->getCacheDir();
        return array_map('unlink', glob("$dir/$table*"));
    }

    /**
     * Check consistency, if there is anything missing, try to deploy it
     */
    private function checkAndDeploy()
    {
        $deployFile = isset($this->_conf['deploy-file'])?$this->_conf['deploy-file']:null;
        $driver = $this->_conf['driver'];

        if (!$deployFile) {
            return null;
        }

        if ($driver == 'mysqli' || $driver == 'pdo_sql') {

        }
    }

    /**
     * @param $table
     * @return mixed
     * @deprecated
     */
    private function fixTableNames($table)
    {
        if (strpos($table, '{') === false && strpos($table, ' ') === false) {
            if (strpos($table, $this->_conf['prefix']) === 0) {
                return $table;
            }
            return $this->_conf['prefix'] . $table;
        }
        $res = preg_replace('/\{(?=\w+\})/', $this->_conf['prefix'], $table);
        return str_replace('}', '', $res);
    }

    /**
     * Adds prefix to table name (if configured)
     * @param $tableName
     * @return mixed
     */
    private function fixTableName($tableName)
    {
        return $this->fixTableNames($tableName);
    }

    /**
     * @return bool
     */
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
     * @return array|bool
     * @throws ErrorException
     */
    private function dropCache()
    {
        if (!$this->getConfigValue('enable-cache')) {
            return false;
        }
        $dir = $this->getCacheDir();
        return array_map('unlink', glob("$dir/*"));
    }

    /**
     * @param $query
     *
     * @return bool|array
     */
    private function safeDropCache($query)
    {
        if (preg_match('/(update|delete|insert)/', strtolower($query)) == 1) {
            // @todo: clear cache only for given tables
            return $this->dropCache();
        }
        return false;
    }

    /**
     * @return array|bool
     */
    public function purgeAllCaches()
    {
        return $this->dropCache();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->_conf;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getConfigValue($name)
    {
        return isset($this->_conf[$name])?$this->_conf[$name]:null;
    }

    /**
     * Simple query executor, expects results and returns them as an assoc array
     * @param $query
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function executeRawQuery($query, array $params = array())
    {
        try {
            $this->result = $this->_adapter->query($this->fixTableName($query), $params);
        } catch (\Exception $e) {
            $this->result = new ResultSet(ResultSet::TYPE_ARRAY, []);
            $mysqli = $this->_adapter->getDriver()->getConnection()->getResource();
            error_log("ERROR: ADB : executeRawQuery");
            error_log("Engine message: " . isset($mysqli->error)?$mysqli->error:"No message available");
            error_log($e->getMessage());
            error_log("query: $query");
            error_log("params: " . print_r($params, true));
            error_log($e->getTraceAsString());
            $throw = true;
            if (isset($this->_conf['suppress-exceptions'])) {
                if ($this->_conf['suppress-exceptions']) {
                    $throw = false;
                }
            }
            if (!isset($this->_conf['throw-engine-message'])) {
                // throw engine message by default
                $this->_conf['throw-engine-message'] = true;
            }
            if ($throw) {
                if (isset($mysqli->error) && $this->_conf['throw-engine-message']) {
                    throw new \Exception($mysqli->error);
                } else {
                    throw $e;
                }
            }
        }
        $this->safeDropCache($query);
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
            $statement = $this->_adapter->createStatement($this->fixTableName($query));
            $res = $statement->execute($params);
            $this->safeDropCache($query);
        } catch (\Exception $e) {
            $this->result = new ResultSet(ResultSet::TYPE_ARRAY, []);
            error_log("ERROR: ADB : executePreparedQuery");
            error_log("query: $query");
            error_log("params: " . print_r($params, true));
            error_log($e->getTraceAsString());
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
    public function fetchAll($table, array $conditions = [], $columns = '*',
        $orderColumn = null, $orderDirection = 'asc')
    {
        $cachedResult = $this->fetchFromCache($table, $conditions, $columns);
        if ($cachedResult !== null && is_array($cachedResult)) {
            return $cachedResult;
        }

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
        } else {
            $vals = [];
        }
        if ($orderColumn !== null) {
            $query .= " order by $orderColumn $orderDirection";
        }
        try {
            $buffer = $this->executeRawQuery($query, $vals);
            if (@$buffer->valid()) {
                $data = $buffer->toArray();
            } else {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }
        $this->storeInCache($table, $conditions, $columns, $data);
        return $data;
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
        if (is_array($result)) {
            return array_shift($result);
        } else {
            return $result;
        }
    }

    /**
     * Returns value from given column
     * @param $table
     * @param array $conditions
     * @param $column
     * @return string
     */
    public function fetchSingleValue($table, array $conditions, $column)
    {
        $buf = $this->fetchOne($table, $conditions, $column);
        if (empty($buf)) {
            return null;
        }
        return $buf[$column];
    }

    /**
     * Fetches selected row as comma separated string
     *
     * @param       $table
     * @param       $column
     * @param array $conditions
     * @return string
     */
    public function fetchList($table, $column,  array $conditions = [])
    {
        $buffer = $this->fetchAll($table, $conditions, $column);
        $array = array_column($buffer, $column);
        return implode(',', $array);
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
     * @deprecated
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
     * @return int id of inserted record
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
        $this->clearTableCache($table);
        $this->executeRawQuery($query, $values);
        return $this->lastInsertId();
    }

    /**
     * Deleted one or more records from given table
     * @param $table
     * @param array $conditions
     * @return int amount of deleted records
     */
    public function deleteRecords($table, array $conditions)
    {
        if (empty($conditions)) {
            throw new InvalidArgumentException("Conditions can not be empty.");
        }
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
        $this->clearTableCache($table);
        return $this->result->getAffectedRows();
    }

    /**
     * Deletes one or more records
     * @param       $table
     * @param array $conditions
     *
     * @return int amount of deleted records
     */
    public function delete($table, array $conditions)
    {
        return $this->deleteRecords($table, $conditions);
    }

    /**
     * Deletes single record. If more than one record fits conditions
     * method returns false and no records are deleted
     * @param $table
     * @param array $conditions
     * @return array|bool
     */
    public function deleteSingleRecord($table, array $conditions)
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
        $this->clearTableCache($table);
        return $this->executeRawQuery($query, $values);
    }

    /**
     * Updates record with given key value
     * if no key value given, value of ID key is assumed
     * if no key given and no ID defined, exception is thrown
     * @param $tableName
     * @param array $record
     * @param null $uniqueKey
     * @return mixed
     * @throws \Exception
     */
    public function updateRecords($tableName, array $record, $uniqueKey = null)
    {
        $tableName = '{' . $tableName . '}';
        if (empty($uniqueKey)) {
            if (!isset($record['id'])) {
                throw new \Exception(
                    'Invalid data given to updateRecords method. '
                    . ' You must specify unique key or provide record ID'
                );
            }
            $uniqueKey = 'id';
        }
        if (!isset($record[$uniqueKey])) {
            throw new \Exception('Invalid unique key given');
        }
        if (empty($record[$uniqueKey])) {
            throw new \Exception('Unique key is empty');
        }
        $fields = [];
        $keys = [];
        $uniqueKeyValue = $record[$uniqueKey];
        // do not update unique key
        unset($record[$uniqueKey]);
        foreach($record as $key => $field) {
            if ($key != $uniqueKey) {
                $fields[] = $field;
                $keys[] = $key . ' = ? ';
            }
        }
        $keys = implode(',', $keys);
        $fields[] = $uniqueKeyValue;
        $query = "update $tableName set $keys where $uniqueKey = ?";
        $this->executeRawQuery($query, $fields)->count();
        $this->clearTableCache($tableName);
        return $this->result->getAffectedRows();
    }

    /**
     * Updates record with given key value
     * if no key value given, value of ID key is assumed
     * if no key given and no ID defined, exception is thrown
     * @param $tableName
     * @param array $record
     * @param null $uniqueKey
     * @return mixed
     * @throws \Exception
     */
    public function update($tableName, array $record, $uniqueKey = null)
    {
       return $this->updateRecords($tableName, $record, $uniqueKey);
    }

    /**
     * Removes table from database
     * @param $table
     * @return array
     */
    public function dropTable($table)
    {
        if ($this->isDropAllowed()) {
            $this->clearTableCache($table);
            return $this->executeRawQuery('drop table {' . $table . '}');
        } else {
            return false;
        }
    }

    /**
     * Will be made private in next version
     *
     * @param $tableName
     * @param $columns
     * @param null $options
     * @param null $keys
     * @return bool
     */
    public function insertTable($tableName, array $columns, $options = null, $keys = null)
    {
        $tname = '{' . $tableName . '}';
        $cols = [];
        foreach ($columns as $name => $definition) {
            $notNull = isset($definition['notnull'])?'not null':'';
            $auto = isset($definition['auto'])?'auto_increment':'';
            $cols[] = "$name $definition[type]($definition[length]) $notNull $auto";
        }
        $colsDefs = implode(',', $cols);
        $this->executeRawQuery("create table $tname ($colsDefs)");
        return true;
    }

    /**
     * @param $tableName
     * @param $columns
     * @param null $options
     * @param null $keys
     * @return bool
     */
    public function createTable($tableName, array $columns, $options = null, $keys = null)
    {
        return $this->insertTable($tableName, $columns, $options, $keys);
    }

    /**
     * Checks if table exists
     * @param $tableName
     * @return bool
     */
    public function tableExists($tableName)
    {
        $res = $this->executeRawQuery('show tables like \'{'.$tableName.'}\'')->toArray();
        if (count($res) > 0) {
            return true;
        }
        return false;
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

    /**
     * Runs query stored in a file
     * by default it is in storage directory
     *
     * @param string $name
     * @param array  $params
     *
     * @return array|bool|int
     * @throws ErrorException
     */
    public function runStoredQuery($name, array $params = [])
    {
        if (!isset($this->_conf['enable-storage'])) {
            throw new ErrorException(
                "Stored queries are not enabled. "
                . "You can enable it in config file."
            );
        }

        if (!isset($this->_conf['storage-dir'])) {
            throw new ErrorException(
                "No storage dir set. You need to set it in config file."
            );
        }

        if (!is_dir($this->_conf['storage-dir'])) {
            throw new  ErrorException(
                "Storage directory is not valid."
            );
        }

        $path = $this->_conf['storage-dir'] . '/' . $name . '.sql';

        if (!file_exists($path)) {
            throw new ErrorException(
                "Required stored query could not be found."
            );
        }

        $query = file_get_contents($path);

        $this->safeDropCache($query);
        return $this->executePreparedQuery($query, $params);
    }

    /**
     * Runs query stored in a file
     * by default it is in storage directory
     *
     * @param string $name
     * @param array  $params
     *
     * @return array|bool|int
     * @throws ErrorException
     */
    public function rsq($name, array $params = [])
    {
        return $this->runStoredQuery($name, $params);
    }

    /**
     * Runs query stored in a file
     * by default it is in storage directory
     *
     * @param string $name
     * @param array  $params
     *
     * @return array|bool|int
     * @throws ErrorException
     */
    public function sql($name, array $params = [])
    {
        return $this->runStoredQuery($name, $params);
    }

    /**
     * Returns true if storage is enabled
     *
     * @return bool
     */
    public function isStorageEnabled()
    {
        if (!isset($this->_conf['enable-storage'])) {
            return false;
        }
        if (!$this->_conf['enable-storage']) {
            return false;
        }
        return true;
    }
}