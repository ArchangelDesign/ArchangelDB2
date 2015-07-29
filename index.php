<?php

/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

require 'autoload_register.php';

$adb = new \ArchangelDB\ADB2();

$res = $adb->fetchAll('users', ['date' => 234]);
//$res = $adb->executeRawQuery("select * from {users} where 1=1");
$prof = $adb->getLastQuery();

var_dump($res);
var_dump($prof);