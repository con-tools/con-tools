<?php

class Model_Event_Tag_Type extends ORM {
	
	protected $_belongs_to = [
			'convention' => []
	];
	
	protected $_has_many = [
			'event_tag_values' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'convention_id' => [],
			// data fields
			'title' => [],
			'requirement' => [],
			'visible' => [ 'type' => 'boolean' ],
	];
}
