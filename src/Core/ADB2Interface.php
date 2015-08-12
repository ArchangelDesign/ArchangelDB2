<?php

/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

namespace ArchangelDB;

interface ADB2Interface
{
    public function executeRawQuery($query, array $params = array());
    public function executePreparedQuery($query, $params = null);
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
    public function insertTable($tableName, $columns, $keys);
    public function dropTable($table);
    public function deleteSingleRocord($table, array $conditions);
    public function deleteRocords($table, array $conditions);
    public function beginTransaction();
    public function commitTransaction();
    public function rollbackTransaction();
}