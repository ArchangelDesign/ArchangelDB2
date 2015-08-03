<?php

/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

require 'autoload_register.php';

$adb = new \ArchangelDB\ADB2();

$res = $adb->insert('users', ['name' => 'Pascal', 'surname' => 'Picolo', 'date' => 'now()']);
var_dump($adb->getLastQuery());
var_dump($adb->lastInsertId());

var_dump($adb->updateRecords('users', ['id' => 34, 'name' => 'Pascal', 'date' => '321321321']));
var_dump($adb->getLastQuery());

var_dump($adb->fetchAll('users'));