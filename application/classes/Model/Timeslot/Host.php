<?php

class Model_Timeslot_Host extends ORM {
	
	protected $_belongs_to = [
			'user' => [],
			'timeslot' => [],
	];
	
}