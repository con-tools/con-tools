<?php

class Model_Coupon_Type extends ORM {
	
	protected $_belongs_to = [
			'convention' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'convention_id' => [],
			// data fields
			'title' => [],
			'discount_type' => [ 'type' => 'enum', 'values' => [ 'percent', 'fixed' ] ],
			'amount' => [ 'type' => 'decimal' ],
			'category' => [],
			'multiuse' => [ 'type' => 'boolean' ], // allow multiple coupons of the same category or not
			'code' => [],
	];
	
};
