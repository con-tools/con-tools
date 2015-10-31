<?php

class Model_Event_Tag extends ORM {

	protected $_belongs_to = [
			'event' => [],
			'event_tag_value' => [],
	];
	
};
