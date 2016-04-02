<?php

class Model_Coupon extends ORM {
	
	protected $_belongs_to = [
			'user' => [],
			'coupon_type' => [],
			'ticket' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'user_id' => [],
			'coupon_type_id' => [],
			'ticket_id' => [],
			// data fields
			'amount' => [ 'type' => 'decimal' ],
	];
	
	public static function byConvention(Model_Convention $con) {
		return (new Model_Coupon())->with('coupon_type')->where('convention_id','=',$con->pk())->find_all();
	}
	
	public static function unconsumedForUser(Model_User $user, Model_Convention $con) {
		return (new Model_Coupon())->with('coupon_type')
				->where('user_id','=',$user->pk())
				->where('convention_id','=',$con->pk())
				->where('ticket_id', 'IS', 'NULL')
				->find_all();
	}

	public static function persist(Model_Coupon_Type $coupon, Model_User $user, $amount = null) {
		$o = new Model_Coupon();
		$o->user = $user;
		$o->coupon_type = $coupon;
		$o->amount = $amount ?: $coupon->amount;
		$o->save();
		return $o;
	}
	
	public function for_json() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return in_array($key, [ 'sale-id', 'amount']);
		},ARRAY_FILTER_USE_KEY), [
				'user' => $this->user->for_json(),
				'type' => $this->coupon_type->for_json(),
				'convention' => $this->coupon_type->convention->for_json(),
		]);
	}
	
};
