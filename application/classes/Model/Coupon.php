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
			'object_id' => [], // the object that consumed this coupon - currently a ticket_id or a user_pass_id
			// data fields
			'object_type' => [ 'type' => 'enum', 'values' => [ 'ticket', 'user_pass' ] ], // description of the type of object that consumed this coupon
			'value' => [ 'type' => 'decimal' ],
			'created_time' => [ 'type' => 'DateTime' ],
			'reason' => '',
	];
	
	public static function byConvention(Model_Convention $con) {
		return (new Model_Coupon())->with('coupon_type')->where('convention_id','=',$con->pk())->find_all();
	}
	
	public static function byConventionUser(Model_Convention $con, Model_User $user) {
		return (new Model_Coupon())->with('coupon_type')
				->where('convention_id','=',$con->pk())
				->where('user_id','=',$user->pk())
				->find_all();
	}
	
	public static function unconsumedForUser(Model_User $user, Model_Convention $con) {
		return (new Model_Coupon())->with('coupon_type')
				->where('user_id','=',$user->pk())
				->where('convention_id','=',$con->pk())
				->where('object_id', 'IS', DB::expr("NULL"))
				->find_all();
	}

	/**
	 * Create a new coupon
	 * @param Model_Coupon_Type $coupon type of coupon being created
	 * @param Model_User $user user that owns the coupon
	 * @param string $reason Reason for assigning this coupon (free text)
	 * @param string|float $value value of the coupon being assigned - if null, read from coupon
	 * @param Model_Sale_Item $sale_item ticket or user_pass that consumes this ticket, if creating a user coupon
	 * @return Model_Coupon
	 */
	public static function persist(Model_Coupon_Type $coupon, Model_User $user, $reason, $value = null, Model_Sale_Item $sale_item = null) : Model_Coupon{
		$o = new Model_Coupon();
		$o->user = $user;
		$o->coupon_type = $coupon;
		$o->value = $value ?? $coupon->value;
		if ($sale_item)
			$o->sale_item = $sale_item;
		$o->created_time = new DateTime();
		$o->reason = $reason;
		$o->save();
		return $o;
	}
	
	public function set($column, $value) {
		switch ($column) {
			case 'ticket':
			case 'user_pass':
			case 'sale_item':
				if (is_null($value))
					$this->object_type = null;
				elseif ($value instanceof Model_Ticket)
					$this->object_type = 'ticket';
				elseif ($value instanceof Model_User_Pass)
					$this->object_type = 'user_pass';
				else
					throw new Exception('Invalid sale_item type '. get_class($value));
				return parent::set('object_id', is_null($value) ? null : $value->pk());
			default:
				return parent::set($column, $value);
		}
	}
	
	public function get($column) {
		switch ($column) {
			case 'ticket':
				if ($this->object_type == 'ticket')
					return $this->sale_item;
				return new Model_Ticket();
			case 'user_pass':
				if ($this->object_type == 'user_pass')
					return $this->sale_item;
				return new Model_User_Pass();
			case 'sale_item':
				switch ($this->object_type) {
					case 'ticket':
						return new Model_Ticket($this->object_id);
					case 'user_pass':
						return new Model_User_Pass($this->object_id);
					default:
						Logger::error("Invalid sale item type '{$this->object_type}'!");
						return new Model_User_Pass(); // invalid item, but at least it has a table
				}
			default:
				return parent::get($column);
		}
	}
	
	public function isFixed() {
		return $this->coupon_type->isFixed();
	}
	
	public function isMultiuse() {
		return $this->coupon_type->isMultiuse();
	}
	
	public function alreadyUsesMultiuse(Model_Sale_Item $item) {
		return (new Model_Coupon())
				->where('coupon_type_id', '=', $this->coupon_type_id)
				->where('object_type', '=', $item->getTypeName())
				->where('object_id','=',$item->pk())->count_all() > 0;
	}
	
	/**
	 * Consume this coupon for a discount on the specified ticket.
	 * The ticket provided will have its price reduced by the relevant value.
	 * Note: this method asssumes that all consumptions of multiple coupons happen
	 * in a database transaction.
	 * @param Model_Sale_Item $item Ticket or user pass that is consuming this coupon
	 */
	public function consume(Model_Sale_Item $item) {
		if ($this->object_id) // sanity - we already have a ticket
			throw new Exception("Trying to double consume coupon " . $this->pk() . " for " . $item->getTypeName() . " " . $item->pk() . "!");
		
		if ($item->price <= 0)
			return; // no need to consume this coupon
		
		if ($this->isMultiuse()) {
			// multi use coupons mean we generate a duplicate and list it as being used, while
			// the main coupon is free to be used again - but not by the same ticket, so we check for that.
			if ($this->alreadyUsesMultiuse($item))
				return; // duplicate use is not allowed
			
			// safe to use
			Model_Coupon::persist($this->coupon_type, $this->user, "spawn multi-use clone", null, $item);
			if ($this->isFixed()) {
				$item->price -= $this->value;
				if($item->price < 0) $item->price=0;
			} else {
				$item->price -= $item->price * $this->value / 100; // "value" says "percent discount"
			}
			$item->save();
			return;
		}
		
		// one use coupons are simply consumed
		if ($this->value > $item->price) {
			Model_Coupon::persist($this->coupon_type, $this->user, "split large coupon", $item->price, $item);
			$this->value -= $item->price;
			$item->price = 0;
			$this->save();
		} else {
			$item->price -= $this->value;
			$this->sale_item = $item;
			$this->save();
		}
		$item->save();
	}
	
	/**
	 * Release this coupon from the ticket. Makes it available for further reuse
	 * Note: if this is a multiuse coupon, we assumed that its a copy and we just
	 * destroy it.
	 */
	public function release() {
		if ($this->isMultiuse()) {
			$this->delete();
			return;
		}
		
		$this->sale_item = null;
		$this->save();
	}

	public function for_json_With_tickets() {
		$user = $this->user->for_json();
		$type = $this->coupon_type->for_json();
		$con = $this->coupon_type->convention->for_json();
		$out = array_merge(array_filter(parent::for_json(),function($key){
			return in_array($key, [ 'id', 'value' ]);
		},ARRAY_FILTER_USE_KEY), [
				'user' => $user,
				'type' => $type,
				'convention' => $con,
				'used' => !is_null($this->object_id),
		]);
		if ($this->object_id) {
			$out[$this->object_type] = $this->sale_item->for_json();
		}
		return $out;
	}

	public function for_json() {
		$user = $this->user->for_json();
		$type = $this->coupon_type->for_json();
		$con = $this->coupon_type->convention->for_json();
		return array_merge(array_filter(parent::for_json(),function($key){
			return in_array($key, [ 'id', 'value', 'object_id', 'object_type' ]);
		},ARRAY_FILTER_USE_KEY), [
				'user' => $user,
				'type' => $type,
				'convention' => $con,
				'used' => !is_null($this->object_id),
		]);
	}
	
};
