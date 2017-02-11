<?php

/**
 * @var \ArchangelDB\ADB2Interface
 */
global $adb;

echo "\n==== FETCH ====\n\n";

if (!$adb->tableExists('orders')) {
    echo "Table is missing. Trying to create... ";

    try {
        $adb->createTable('orders', getOrderStructure());
        echo "[OK]\n";
    } catch (Exception $e) {
        echo "[FAILED]\n";
        goDead("Failed to create table orders");
    }
}

function getOrderStructure()
{
    return [
        'id' => [
            'type' => 'INT',
            'length' => 11,
            'notnull' => true,
            'autoincrement' => true,
            'primary' => true,
        ],
        'username' => [
            'type' => 'VARCHAR',
            'length' => 50,
            'default' => "'-none-'",
        ],
        'status' => [
            'type' => 'TINYINT',
            'length' => 1,
            'default' => 0,
        ],
        'date' => [
            'type' => 'DATETIME'

        ],
    ];
}