<?php defined('SYSPATH') OR die('No direct access allowed.');

$dsn = parse_url(getenv(getenv('DB_URL_ENV_NAME'))); // looks like mysql://user:pass@host/database?reconnect=true

return [

    'default' => [
        'type'           => 'MySQLi',
        'connection'     => [
            'hostname'      => $dsn['host'],
            'username'      => $dsn['user'],
            'password'      => $dsn['pass'],
            'database'      => explode('/',$dsn['path'])[1],
            'persistent'    => TRUE,
            'port'			=> 3306,
        ],
        'table_prefix'      => '',
        'charset'           => 'utf8',
        'caching'           => TRUE,
        'profiling'         => FALSE,
    ],

];
