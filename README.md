# ArchangelDB module for Zend Framework 2
## copyright (c) www.archangel-design.com

** Simple but powerful PHP Database Engine **

Another version of Archangel DB introduces much more than the previous one.
This time not only allowing transaction and prepared statements but also
compatible with Postgres, Oracle, Sqlite, SQLServer and IBM DB2.

Designed for Zend Framework 2 (using Zend\DB module) but can be implemented
in every PHP 5.5 project.

Advantages
- lightweight
- very simple to use, 2 lines of code to initialize
- flexible configuration
- ability to add common table prefix
- easy access to profiler (execution time, full query, query count...)
- no external dependencies
- utilizing Zend\Db : stable and efficient module
- fully compatible with ArchangelDB 1.2

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
    'prefix' => 'adb_',
    'profiler' => true,
    'options' => [
        'buffer_results' => true,
    ]
]
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


