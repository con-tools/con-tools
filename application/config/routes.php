<?php

return [
		
	['default' => [
		'uri' => '(<controller>(/<action>(/<id>)))',
		'defaults' => [
			'controller' => 'welcome',
			'action'     => 'index',
		],
	]],
	[ 'crud' => [
		'uri' => 'entities/<controller>(/<id>)',
		'defaults' => [
			'directory' => 'Entities',
			'action' => 'index',
		],
	]],
	

];
