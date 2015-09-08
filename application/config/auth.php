<?php

$auth_token = explode(':', $_SERVER['GOOGLE_AUTH_CLIENT']);

return [
		'google' => [
				'type'		=> 'OpenIDConnect',
				'id'		=> $auth_token[0],
				'secret'	=> $auth_token[1],
				'endpoint'	=> 'https://accounts.google.com/o/oauth2/auth',
		],
];
