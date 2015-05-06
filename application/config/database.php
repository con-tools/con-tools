<?php defined('SYSPATH') OR die('No direct access allowed.');

$dsn = parse_url($_SERVER[$_SERVER['DB_URL_ENV_NAME']]); // looks like mysql://user:pass@host/database?reconnect=true

return [

    'default' => [
        'type'           => 'MySQLi',
        'connection'     => [
            'hostname'      => $dsn['host'],
            'username'      => $dsn['user'],
            'password'      => $dsn['pass'],
            'database'      => $dsn['path'],
            'persistent'    => TRUE,
            'port'			=> 3306,
        ],
        'table_prefix'      => '',
        'charset'           => 'utf8',
        'caching'           => FALSE,
        'profiling'         => FALSE,
    ],

];
