<?php

/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

namespace ArchangelDB;

/**
 * Class AbstractDeployer
 * @package ArchangelDB
 */
abstract class AbstractDeployer
{
    /**
     * @var ADB2
     */
    protected $_adb = null;

    const ADB_HIST_TABLE = 'adb_database_history';

    private function deployHistoryTable()
    {
        $structure = [
            'id' => [
                'type' => 'integer',
                'length' => 11,
                'notnull',
                'auto'
            ],
            'checksum' => [
                'type' => 'varchar',
                'length' => 200,
                'notnull'
            ],
            'date' => [
                'type' => 'varchar',
                'length' => 20
            ],
            'author' => [
                'type' => 'varchar',
                'length' => 120
            ]
        ];
        $this->_adb->insertTable(self::ADB_HIST_TABLE, $structure);
    }

    protected function getDatabaseVersion()
    {
        if (!$this->_adb->tableExists(self::ADB_HIST_TABLE)) {
            $this->deployHistoryTable();
        }
    }
}