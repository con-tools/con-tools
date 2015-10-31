<?php

class Model_Sale extends ORM {
	
	protected $_belongs_to = [
			'user' => [],
			'cashier' => [ 'model' => 'user', 'foreign_key' => 'cashier_id' ],
			'sale' => [ 'model' => 'sale', 'foreign_key' => 'original_sale_id' ],
	];
	
	protected $_has_many = [
			'tickets' => [],
			'coupons' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'user_id' => [],
			'cashier_id' => [],
			'transaction_id' => [],
			'original_sale_id' => [], // if not null, this is a cancellation transaction, 
			// and transaction_id is the cancellation confirmation. refer to original sale for actual transaction ID
	];
	
};
