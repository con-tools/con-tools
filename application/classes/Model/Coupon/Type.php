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
	
	public static function persist(Model_Convention $con, string $title, $fixed_discount, $amount,
				string $category, $multiuse, $code = null) : Model_Coupon_Type {
		$o = new Model_Coupon_Type();
		$o->convention = $con;
		$o->title = $title;
		$o->discount_type = $fixed_discount ? 'fixed' : 'percent';
		$o->amount = $amount;
		$o->category = $category;
		$o->multiuse = $multiuse;
		$o->code = $code;
		$o->save();
		return $o;
	}

	public static function byConvention(Model_Convention $con) : Database_Result {
		return (new Model_Coupon_Type())->where('convention_id','=',$con->pk())->find_all();
	}
	
	public function for_json() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return $key != 'convention_id';
		},ARRAY_FILTER_USE_KEY), [
				'convention' => $this->convention->for_json(),
		]);
	}
	
};
