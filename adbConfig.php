<?php

/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 *
 * Sample configuration file. This file returns an array
 * for ConfigLoader, however you can just pass it as an
 * argument for ADB2 constructor.
 */

return array(
    'driver' => 'pgsql',
    'dbname' => 'mis3',
    'dbuser' => 'admin',
    'dbpass' => 'admin',
    'prefix' => 'adb_',
    'profiler' => true,
);