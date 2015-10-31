<?php

class Model_Coupon extends ORM {
	
	protected $_belongs_to = [
			'user' => [],
			'coupon_type' => [],
			'sale' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'user_id' => [],
			'coupon_type_id' => [],
			'sale_id' => [],
			// data fields
			'amount' => [ 'type' => 'decimal' ],
	];
	
};
