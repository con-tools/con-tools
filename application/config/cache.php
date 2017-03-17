<?php
return [
	'default'    => [
		'driver'             => 'file',
		'cache_dir'          => APPPATH.'cache',
		'default_expire'     => 3600,
		'ignore_on_delete'   => [
			'.gitignore',
			'.git',
			'.svn'
		]
	]
];
