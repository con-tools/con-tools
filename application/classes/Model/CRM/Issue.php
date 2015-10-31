<?php

class Model_CRM_Issue extends ORM {
	
	protected $_belongs_to = [
			'agent' => [ 'model' => 'user', 'foreign_key' => 'agent_id', 'nullable' => true ],
			'queue' => [ 'model' => 'crm_queue' ],
			'event' => [ 'nullable' => true ],
	];
	
	protected $_has_many = [
			'messages' => [ 'model' => 'crm_messages' ],
	];
	
	protected $_columns = [ 
			'id' => [],
			// foreign key
			'agent_id' => [],
			'crm_queue_id' => [],
			'event_id' => [],
			// data
			'title' => [],
			'status' => [ 'type' => 'enum', values => [ 
					'unassigned',
					'open',
					'awaiting-approval',
					'ready-for-timeslotting',
					'closed',
			] ],
	];
	
};
