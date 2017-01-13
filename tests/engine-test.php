<?php
/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

print "Testing ADB2...\n";

require '../autoload_register.php';

function goDead($msg) {
    print("====== FAILED ======\n");
    print($msg."\n");
    die;
}

try {
    $adb = new \ArchangelDB\ADB2();
} catch (Exception $e) {
    print "Failed to create ADB \n".$e->getMessage(); die();
}

$path = dirname(__DIR__) . '/database-structure.xml';

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

print("[OK] all well so far.\n");

if (!$adb->columnExists('users', 'date')) {
    goDead("columnExists(date) failed.");
}

print("clearing table...\n");
$adb->deleteRecords('users', ['1' => '1']);

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

print("[OK] insert works fine.\n");

/**
 * ===== UPDATE TEST =====
 */
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

$adb->deleteRecords('users', [1 => 1]);
$adb->insert('users', ['name' => 'Archangel', 'date' => '1']);
$adb->insert('users', ['name' => 'Design', 'date' => '1']);
$adb->updateRecords('users', ['name' => 'theName', 'surname' => 'theSurname', 'date' => '1'], 'date');
$records = $adb->fetchAll('users');

if (count($records) !== 2) {
    goDead("update failed. Wrong amount of records returned.");
}

foreach ($records as $record) {
    if ($record['name'] !== 'theName' || $record['surname'] !== 'theSurname') {
        goDead("update failed. Wrong data returned. " . print_r($records, true));
    }
}

print("[OK] updates work fine.\n");

/**
 *  ===== FETCH LIST =====
 */

print("running fetchList test...\n");
$adb->deleteRecords('users', ['1' => '1']);
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

print("[OK] fetchList works fine.\n");

/**
 * ===== STORED QUERIES =====
 */
if ($adb->isStorageEnabled()) {
    print("testing stored queries...\n");
    print("dropping data...\n");
    $adb->deleteRecords('users', ['1' => '1']);
    print("testing insert...\n");
    $adb->sql('insert-test', ['a', 'b', '2']);
    print("fetching...\n");
    $res = $adb->sql('fetch-test', [2]);
    if (empty($res)) {
        goDead("stored query insert or fetch failed.");
    }
    print("testing delete...\n");
    $adb->sql('delete-test', [2]);
    $all = $adb->fetchAll('users');
    if (!empty($all)) {
        goDead("stored delete query failed.");
    }
    print("[OK] storage engine seems well.\n");
}

print("testing cache...\n");
if (!$adb->getConfigValue('enable-cache')) {
    goDead("Cache is not enabled. Can not continue.");
}

$adb->delete('users', [1 => 1]);
$adb->insert('users', ['name' => 'AD', 'surname' => 'DA', 'date' => 234]);
$rec = $adb->fetchOne('users', ['date' => 234]);

if ($rec['name'] !== 'AD' || $rec['surname'] !== 'DA') {
    goDead("Wrong data returned");
}
// test twice
$rec = $adb->fetchOne('users', ['date' => 234]);
if ($rec['name'] !== 'AD' || $rec['surname'] !== 'DA') {
    goDead("Wrong data returned");
}

$adb->update('users', ['name' => 'DD', 'surname' => 'AA', 'date' => 234], 'date');

$rec = $adb->fetchOne('users', ['date' => 234]);
if ($rec['name'] !== 'DD' || $rec['surname'] !== 'AA') {
    goDead("Wrong data returned \n" . print_r($rec));
}

$adb->delete('users', ['date' => 234]);
$rec = $adb->fetchAll('users');

if (!empty($rec)) {
    goDead("Cache returned out-of-date data");
}

print("[OK] cache system works fine.\n");

print("error handling tests...\n");
if (isset($adb->getConfig()['suppress-exceptions'])) {
    $supression = $adb->getConfig()['suppress-exceptions'];
    print("Exception supression is " . ($supression ? " ON \n":" OFF \n"));
} else {
    $supression = false;
    print("Exception supression is OFF\n");
}
try {
    $adb->executeRawQuery("select * from {nonexistingtable} where a=1");
    $adb->fetchAll('nonexistingtable', ['od' => 'do']);
    if (!$supression)
        goDead("error handling test failed.");
} catch (Exception $e) {
    if ($supression)
        goDead("error handling test failed.");
}

print("[OK] error handling seems fine.\n");

print("single quote test...\n");
try {
    $adb->insert('users', ['name' => "Connan O'Bian"]);
    $check = $adb->fetchOne('users', ['name' => "Connan O'Bian"]);
    if (empty($check)) {
        goDead("single quote test failed\n");
    }
    if ($check['name'] !== "Connan O'Bian") {
        goDead("single quote test failed\n");
    }
} catch (Exception $e) {
    goDead("single quote test failed\n");
}
print("single quote test passed\n");

print("running stress test...\n");
$adb->delete('users', ['1' => '1']);
$totalRecords = 100;
$multiplier = $totalRecords / 10;
$i = $totalRecords;
$percent = 0;
print('00%');
while ($i > 0) {
    $adb->insert('users', ['name' => 'Archangel', 'surname' => 'Design', 'date' => $i]);
    $i--;
    if ($i % $multiplier == 0) {
        $percent += 10;
        print("\x08\x08\x08$percent%");
    }
}
print("\n");

$data = $adb->fetchAll('users', [], 'date', 'date', 'desc');
if (count($data) != $totalRecords) {
    goDead("Invalid number of records ".count($data));
}

print("[OK] record count correct.\n");
$firstOne = array_shift($data);

if ($firstOne['date'] != $totalRecords) {
    goDead("It seems that sorting does not work properly. ($firstOne[date])");
}
print("[OK] sorting correct.\n");

print("Checking filters...\n");
$param = "-- select drop -- %$? ";
$param = $adb->filterSqlArgument($param);
if ($param != "") {
    goDead("Filter SQL Argument did not react properly.");
}
print("[OK]\n");

print("Test sequence completed.\n\n");
print("****** SUCCESS ******\n\n\n");