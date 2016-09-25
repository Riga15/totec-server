<?php

use \Phalcon\Config;

defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

return new Config([
    'database' => [
        'adapter' => 'Mysql',
        'host' => 'xxx.xxx.xxx.xxx',
        'username' => 'totec',
        'password' => 'Totec123$',
        'dbname' => 'db1',
        'charset' => 'utf8mb4',
    ],
    'debug' => [
        'error' => false,
    ],
]);
