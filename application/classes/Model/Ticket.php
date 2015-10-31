<?php

class Model_Ticket extends ORM {
	
	protected $_belongs_to = [
			'user' => [],
			'timeslot' => [],
			'sale' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'user_id' => [],
			'timeslot_id' => [],
			'name' => [],
			'status' => [ 'type' => 'enum', 'values' => [ 'reserved', 'processing', 'authorized', 'cancelled' ]],
	];
	
};
