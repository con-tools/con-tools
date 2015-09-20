<?php

return array(
	'database' => array(
		'group'   => 'default',
		'table'   => 'api_sessions',
		'gc'      => 500,
		'columns' => array(
			'session_id'  => 'session_id',
			'last_active' => 'last_active',
			'contents'    => 'contents'
		),
	),
);
