<?php

/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'autoload_register.php';

$adb = new \ArchangelDB\ADB2();

$res = $adb->tableExists('users');

$result = $adb->adbfetch('users', [], [], [], ['name']);
var_dump($result);