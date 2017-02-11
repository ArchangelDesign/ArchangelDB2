<?php

/**
 * @var \ArchangelDB\ADB2Interface
 */
global $adb;

echo "\n==== FETCH ====\n\n";

if (!$adb->tableExists('orders')) {
    echo "Table is missing. Trying to create...";

    $adb->createTable('orders', $structure);
}

function getOrderStructure()
{
    return [
        'id' => [
            'type' => 'INT',
            'length' => 11,
            'notnull',
            'autoincrement',
        ]
    ];
}