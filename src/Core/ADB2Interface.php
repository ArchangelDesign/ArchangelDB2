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
    public function query($query);
}