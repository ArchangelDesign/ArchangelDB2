# ArchangelDB module for Zend Framework 2
## copyright (c) www.archangel-design.com

** Simple but powerful PHP Database Engine **

Another version of Archangel DB introduces much more than the previous one.
This time not only allowing transaction and prepared statements but also
compatible with Postgres, Oracle, Sqlite, SQLServer and IBM DB2.

Designed for Zend Framework 2 (using Zend\DB module) but can be implemented
in every PHP 5.5 project.

Advantages
--------------
* lightweight
* very simple to use, 2 lines of code to initialize
* flexible configuration
* ability to add common table prefix
* easy access to profiler (execution time, full query, query count...)
* no external dependencies
* cache system
* ability to run queries stored in files
* XML deployment files
* database structure history 
* automatic database structure updater
* utilizing Zend\Db : stable and efficient module
* fully compatible with ArchangelDB 1.2

So far tested on Mysql and PostgreSQL only.

Examples of how easy it is to start using Archangel DB 2

============================================================================
```
<?php
// initialized with config file adbConfig.php in module or project root
require 'autoload_register.php';
$adb = new \ArchangelDB\ADB2();

$users = $adb->fetchAll('users', ['deleted' => 0, 'active' => 1]);
// $users now holds associative array of users

$adb->deleteRecords('users', ['deleted' => 1]);
// all users with column deleted set to 1 will be deleted from table

$adb->insert('user', ['name' => 'Archangel', 'surname' => 'Design', 'deleted' => 0]);
// user has been inserted to table users

$idList = $adb->fetchList('users', 'id', ['deleted' => 1]);
// returns comma separated list of id as string ( like "1,2,3,4,5")
// for IN statements  
?>
```
============================================================================
```
<?php
// initialized with array as argument
$config = [
    'driver' => 'mysqli',
    'database' => 'lop',
    'username' => 'admin',
    'password' => 'admin',
    'prefix' => 'lop_', // not required, prefix added to all tables (automatically)
    'profiler' => true, // recommended, if disabled some methods will not return results (like getLastQuery())
    'allow_drop' => true, // if false or not set, no drop command is allowed
    'deploy-file' => 'database-structure.xml',
    'storage-dir' => __DIR__ . '/storage',
    'enable-cache' => true,
    'enable-storage' => true, // enables use of queries stored in files
    'allow-deploy' => true, // whether to allow ADB to create the database, defaults to true
    'cache-dir' => __DIR__ . '/cache',
    'options' => [
        'buffer_results' => true, // required for mysql and pgsql
        // if enabled, all results will be buffered. This option is required for ADB to work
        // properly, you can read about implications of this option on database provider site.
        // if not set, it defaults to true
    ]
];
require 'autoload_register.php';
$adb = new \ArchangelDB\ADB2($config);

$users = $adb->fetchAll('users', ['deleted' => 0, 'active' => 1]);
// $users now holds associative array of users
?>
```
==============================================================================
```
// Executing custom SQL
$res = $adb->executeRawQuery("select * from {users} where deleted = ? and active = ?", [0, 1]);

// table prefix will be added automatically
```


