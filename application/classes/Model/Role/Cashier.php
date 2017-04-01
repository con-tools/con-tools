<?php

class Model_Role_Cashier extends Model_Role_Base {
	
	protected $_title = 'Convention Cashier';
	
	protected $_privileges = [
			Model_Role::CAPABILITY_LIST_EVENTS,
			Model_Role::CAPABILITY_CREATE_USER,
			Model_Role::CAPABILITY_CREATE_USER_PASS,
			Model_Role::CAPABILITY_CREATE_TICKET,
			Model_Role::CAPABILITY_CREATE_SALE,
	];
	
};
