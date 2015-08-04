<?php
/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

print "Testing ADB2...\n";

require 'autoload_register.php';

try {
    $adb = new \ArchangelDB\ADB2();
} catch (Exception $e) {
    print "Failed to create ADB \n"; die();
}

