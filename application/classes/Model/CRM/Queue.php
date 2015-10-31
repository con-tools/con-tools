<?php

class Model_CRM_Queue extends ORM {
	
	protected $_belongs_to = [
			'convention' => [],
	];
	
	protected $_has_many = [
			'issues' => [ 'model' => 'crm_issues' ],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign key
			'convention_id' => [],
			// data fields
			'title' => [],
	];
	
};
