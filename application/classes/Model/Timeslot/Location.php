<?php

class Model_Timeslot_Location extends ORM {
	
	protected $_belongs_to = [
			'timeslot' => [],
			'location' => [],
	];

    protected $_columns = [
            'id' => [],
            // foreign keys
            'timeslot_id' => [],
            'location_id' => [],
    ];	
}
