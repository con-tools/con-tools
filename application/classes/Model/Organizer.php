<?php
class Model_Organizer extends ORM {

	protected $_belongs_to = [
		'convention' => [] 
	];

	protected $_columns = [
		'id' => [],
		// foreign keys
		'convention_id' => [],
		// data fields
		'title' => [] 
	];

}
