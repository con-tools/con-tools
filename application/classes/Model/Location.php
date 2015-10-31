<?php

class Model_Location extends ORM {
	
	protected $_belongs_to = [
			'convention' => [],
	];
	
	protected $_has_many = [
			'timeslots' => [ 'model' => 'timeslot', 'through' => 'timeslot_location' ],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'convention_id' => [],
			// data fields
			'title' => [],
			'max_attendees' => [],
			'area' => [],
	];
	
};
