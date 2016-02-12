<?php

Email::$default = getenv('EMAIL_IMPL') ?: Email::$default;

return [
		'native' => [
				'type'		=> 'native',
		],

		'swiftmailer' => [
				'type'		=> 'Swiftmailer',
				'host' 		=> getEnv('SMTP_HOST'),
				'port'		=> getEnv('SMTP_PORT'),
				'user'		=> getEnv('SMTP_USERNAME'),
				'password'	=> getEnv('SMTP_PASSWORD'),
				'tls'		=> (getEnv('SMTP_PORT') != 25),
		],
];
