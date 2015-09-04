<?php

return [
		'google' => [
				'type'		=> 'OpenIDConnect',
				'id'		=> $_SERVER['AUTH_GOOGLE_CLIEND_ID'],
				'secret'	=> $_SERVER['AUTH_GOOGLE_CLIEND_SECRET'],
				'endpoint'	=> 'https://accounts.google.com/o/oauth2/auth',
		],
];
