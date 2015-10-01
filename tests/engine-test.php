<?php
/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

print "Testing ADB2...\n";

require '../autoload_register.php';

function goDead($msg) {
    print("====== FAILED ======");
    print($msg."\n");
    die;
}

try {
    $adb = new \ArchangelDB\ADB2();
} catch (Exception $e) {
    print "Failed to create ADB \n"; die();
}

$path = dirname(__DIR__) . '/database-structure.xml';
//$deployer = new \ArchangelDB\Deployer($adb, $path);
//$deployer->deployDatabaseStructure();

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
    goDead("No users table found in database.");
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

print("clearing table...\n");
$adb->deleteRocords('users', ['1' => '1']);

print("inserting record to users...\n");
$adb->insert('users', ['name' => 'Archangel', 'surname' => 'Design', 'date' => '791']);
$testData = $adb->fetchOne('users', ['name' => 'Archangel', 'surname' => 'Design', 'date' => '791']);
if (!is_array($testData)) {
    goDead("Insert, fetch test failed.");
}

if (!isset($testData['name']) || !isset($testData['surname']) || !isset($testData['date'])) {
    goDead("Fetch data test failed.");
}

if ($testData['name'] != 'Archangel' || $testData['surname'] != 'Design' || $testData['date'] != '791') {
    goDead("fetchOne returned unexpected data.");
}
// to lowercase
print("testing updates...\n");
$testData['surname'] = 'design';
unset($testData['id']);
$adb->updateRecords('users', $testData, 'name');

$testData = $adb->fetchOne('users', ['surname' => 'design']);

if (!$testData) {
    goDead('Update test failed.');
}

if ($testData['surname'] != 'design') {
    goDead("Update test failed.\n");
}

print("running fetchList test...\n");
$adb->deleteRocords('users', ['1' => '1']);
$adb->insert('users', ['name' => 'Archangel', 'surname' => 'Design', 'date' => '791']);
$adb->insert('users', ['name' => 'Design', 'surname' => 'Design', 'date' => '791']);
$list = $adb->fetchList('users', 'name');
$list = explode(',', $list);
if (count($list) !== 2) {
    goDead("fetchList failed.");
}
if (array_shift($list) !== 'Archangel') {
    goDead("fetchList failed.");
}

if (array_shift($list) !== 'Design') {
    goDead("fetchList failed");
}

print("Test sequence completed.\n\n");
print("****** SUCCESS ******\n\n\n");