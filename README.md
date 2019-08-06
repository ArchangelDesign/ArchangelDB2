# ArchangelDB module for PHP 5.5 application
#### copyright (c) www.archangeldesign.github.io

** Simple but powerful PHP Database Engine **

Another version of Archangel DB introduces much more than the previous one.
This time not only allowing transaction and prepared statements but also
compatible with Postgres, Oracle, Sqlite, SQLServer and IBM DB2.
Built-in cache system and error log, easy error handling and deployment.

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
* runs queries stored in filesystem (useful for reports)
* cache system
* ability to run queries stored in files
* XML deployment files
* database structure history 
* automatic database structure updater
* utilizing Zend\Db : stable and efficient module
* fully compatible with ArchangelDB 1.2

So far tested on Mysql and PostgreSQL only.

### Installation via composer

Install composer (linux):

```
curl -sS https://getcomposer.org/installer | php
```

Create "composer.json" file in your project root.
Within a "require" section mention ADB2 as follows

```
{
  "name": "Your project anme",
  "description": "your description",
  "homepage": "http://arcangel-design.com or whatever",
  "require": {
    "php": "^5.6 || ^7.0",
    "archangeldesign/archangeldb2" : "^1.3",
  }
}
```

run composer update/install

```
php composer.phar update
```

Include "autoload.php" from vendor directory and you're done.
Please note that if you are using composer you do not need to
use "autoload_register.php" file and in further version it
will be removed. It is recommended that you always use 
composer to manage your dependencies.

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

$adb->updateRecords('user',
[
    'name' ='Archangel', 
    'surname' = 'System', 
    'deleted' => 1
], 'name');
// record with name = 'Archangel' has been updated (or multiple records)

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
    'error-log-file' => __DIR__ . '/error.log',
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


