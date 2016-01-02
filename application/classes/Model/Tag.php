<?php

/**
 * Free form tagging system for events
 * @author odeda
 */
class Model_Tag extends ORM {

	protected $_belongs_to = [
			'event' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'event_id' => [],
			// data fields
			'title' => [],
	];
}
