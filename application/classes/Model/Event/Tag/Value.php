<?php

class Model_Event_Tag_Value extends ORM {
	
	protected $_belongs_to = [
			'event_tag_type' => [],
	];
	
	protected $_has_many = [
			'event_tags' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'event_tag_type_id' => [],
			// data fields
			'title' => [],
	];
	
};
