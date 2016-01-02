<?php

class Model_Timeslot_Host extends ORM {
	
	protected $_belongs_to = [
			'user' => [],
			'timeslot' => [],
	];
    
    protected $_columns = [
            'id' => [],
            // foreign keys
            'user_id' => [],
            'timeslot_id' => [],
    ];
}