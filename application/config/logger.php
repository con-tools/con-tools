<?php

return [
		'type' => 'logentries',
		'token' => getenv('LOGENTRIES_TOKEN'),
		'hostid' => getenv('HEROKU') ? 'heroku' : 'local',
];
