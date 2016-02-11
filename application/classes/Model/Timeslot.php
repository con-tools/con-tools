<?php

class Model_Timeslot extends ORM {
	
	protected $_belongs_to = [
			'event' => [],
	];
	
	protected $_has_many = [
			'hosts' => [ 'model' => 'user', 'through' => 'timeslot_hosts' ],
			'locations' => [ 'model' => 'location', 'through' => 'timeslot_locations' ],
			'tickets' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'event_id' => [],
			// data fields
			'start_time' => [ 'type' => 'DateTime' ],
			'duration' => [],
			'min_attendees' => [],
			'max_attendees' => [],
			'notes_to_attendees' => [],
	];
}
