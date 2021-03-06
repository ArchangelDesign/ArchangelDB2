<?php

/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

namespace ArchangelDB;

interface ADB2Interface
{
    public function executeRawQuery($query, array $params = []);
    public function executePreparedQuery($query, array $params = []);
    public function fetchAll($table, array $conditions, $columns = '*');
    public function getLastQuery();
    public function getLastQueryTime();
    public function getAll($query);
    public function fetchOne($table, array $conditions, $columns = '*');
    public function fetchSingleValue($table, array $conditions, $column);
    public function query($query);
    public function insert($table, $record);
    public function updateRecords($tableName, array $record, $uniqueKey = null);
    public function lastInsertId();
    public function columnExists($tableName, $columnName);
    public function tableExists($tableName);
    public function createTable($tableName, array $columns, $options = null, $keys = null);
    public function dropTable($table);
    public function deleteSingleRecord($table, array $conditions);
    public function deleteRecords($table, array $conditions);
    public function delete($table, array $conditions);
    public function beginTransaction();
    public function commitTransaction();
    public function rollbackTransaction();
    public function rsq($name, array $params = []);
    public function sql($name, array $params = []);
    public function isStorageEnabled();
    public function filterSqlArgument($argument);
    public function runStoredQuery($name, array $params = []);
    public function insertTable($tableName, array $columns, $options = null, $keys = null);
    public function update($tableName, array $record, $uniqueKey = null);
    public function fetchList($table, $column,  array $conditions = []);
    public function getConfigValue($name);
    public function getConfig();
    public function purgeAllCaches();

}