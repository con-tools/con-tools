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
    		// fields
    		'name' => [],
    ];
    
    public static function persist(Model_Timeslot $timeslot, Model_User $user, $name) : Model_Timeslot_Host {
    	$o = new Model_Timeslot_Host();
    	$o->user = $user;
    	$o->timeslot = $timeslot;
    	$o->name = $name;
    	$o->save();
    	return $o;
    }
}