<?php

/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

require("autoload_register.php");
$adb = new \ArchangelDB\ADB2();
$deployer = new \ArchangelDB\Deployer($adb);

$path = dirname(dirname(__DIR__)) . '/database-structure.xml';
$deployer->setDeployFile($path);
