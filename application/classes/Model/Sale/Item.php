<?php

abstract class Model_Sale_Item extends ORM {
	
	const STATUS_RESERVED = 'reserved';
	const STATUS_PROCESSING = 'processing';
	const STATUS_AUTHORIZED = 'authorized';
	const STATUS_CANCELLED = 'cancelled';
	const STATUS_REFUNDED = 'refunded';

	public static function validStatuses() {
		return [
				self::STATUS_RESERVED,
				self::STATUS_PROCESSING,
				self::STATUS_AUTHORIZED,
		];
	}
	
	/**
	 * Implement this in method to generate the value for the 'object_type' field in referencing models
	 * @return object_type value to use for this instance, such as 'ticket' or 'user_pass'
	 */
	abstract public function getTypeName();

	public function consumeCoupons() {
		if ($this->price <= 0)
			return $this->save(); // no need to consume coupons
		
		Database::instance()->begin(); // work in transactions, in case I need to duplicate coupons
		foreach (Model_Coupon::unconsumedForUser($this->user, $this->convention) as $coupon) {
			$coupon->consume($this);
			if ($this->price <= 0)
				break; // stop consuming coupons, there's no more need
		}
		$this->save();
		Database::instance()->commit();
	}

	public function get($column) {
		switch($column) {
			case 'coupons':
				return parent::get($column)->where('object_type', '=', $this->getTypeName());
			default:
				return parent::get($column);
		}
	}
}
