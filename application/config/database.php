<?php defined('SYSPATH') OR die('No direct access allowed.');

$dsn = parse_url(getenv(getenv('DB_URL_ENV_NAME'))); // looks like mysql://user:pass@host/database?reconnect=true
$opts = [];
foreach (array_map(function($kv){ return explode('=', $kv, 2); },
		array_filter(explode('&',@$dsn['query']),
				function($qv){ return !empty($qv);})) as $kv)
	$opts[urldecode($kv[0])] = urldecode($kv[1]);

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
            'ssl'			=> @$opts['ssl'] ? [
            		'client_key_path' => APPPATH.'config/secure/client-key.pem',
            		'client_cert_path' => APPPATH.'config/secure/client-cert.pem',
            		'ca_cert_path' => APPPATH.'config/secure/server-ca.pem',
            		] : NULL,
            'flags'			=> MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT,
        ],
        'table_prefix'      => '',
        'charset'           => 'utf8',
        'caching'           => TRUE,
        'profiling'         => FALSE,
    ],

];
