<?php
/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

print "Testing ADB2...\n";

require '../autoload_register.php';

function goDead($msg) {
    print($msg."\n");
    die;
}

try {
    $adb = new \ArchangelDB\ADB2();
} catch (Exception $e) {
    print "Failed to create ADB \n"; die();
}

if (!$adb->tableExists('users')) {
    $adb->insertTable('users',
        [
            'name' => [
                'type' => 'varchar',
                'length' => 50
            ],
            'surname' => [
                'type' => 'varchar',
                'length' => 50,
            ],
            'date' => [
                'type' => 'integer',
                'length' => 10,
            ],
        ]
    );
}

print("checking table users...\n");
if (!$adb->tableExists('users')) {
    print("No users table found in database.\n");
    die();
}

print("running column exists test...\n");
if (!$adb->columnExists('users', 'name')) {
    goDead("columnExists(name) failed.");
}

if (!$adb->columnExists('users', 'surname')) {
    goDead("columnExists(surname) failed.");
}

if (!$adb->columnExists('users', 'date')) {
    goDead("columnExists(date) failed.");
}

print("inserting record to users...\n");
$adb->insert('users', ['name' => 'Archangel', 'surname' => 'Design', 'date' => '791']);
$testData = $adb->fetchOne('users', ['name' => 'Archangel', 'surname' => 'Design', 'date' => '791']);
if (!is_array($testData)) {
    print("Insert, fetch test failed.\n");
    die();
}

if (!isset($testData['name']) || !isset($testData['surname']) || !isset($testData['date'])) {
    print("Fetch data test failed.\n");
    die();
}
print("Test sequence completed.\n\n");
print("****** SUCCESS ******\n\n\n");