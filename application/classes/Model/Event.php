<?php

class Model_Event extends ORM {
	
	protected $_belongs_to = [
			'convention' => [],
			'user' => [],
			'staff_contact' => [ 'model' => 'user', 'foreign_key' => 'staff_contact_id' ],
	];
	
	protected $_has_many = [
			'timeslots' => [],
			'event_tags' => [],
			'crm_issues' => [],
			'tags' => [],
			'media' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'user_id' => [],
			'staff_contact_id' => [],
			'convention_id' => [],
			// data fields
			'title' => [],
			'teaser' => [],
			'description' => [],
			'price' => [],
			'requires_registration' => [],
			'duration' => [],
			'min_attendees' => [],
			'max_attendees' => [],
			'notes_to_staff' => [],
			'notes_to_attendees' => [],
			'scheduling_constraints' => [],
	];
	
}