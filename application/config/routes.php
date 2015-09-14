<?php
return [
		'crud2' => [
				'uri' => 'entities/<controller>(/<id>)',
				'defaults' => [
						'directory' => 'Entities',
						'action' => 'index' 
				] 
		],
		
		'default' => [
				'uri' => '(<controller>(/<action>(/<id>)))',
				'defaults' => [
						'controller' => 'welcome',
						'action' => 'index'
				] 
		] 
];
