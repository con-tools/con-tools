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
			'value' => [ 'type' => 'decimal' ],
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

	public static function persist(Model_Coupon_Type $coupon, Model_User $user, $value = null, Model_Ticket $ticket = null) : Model_Coupon{
		$o = new Model_Coupon();
		$o->user = $user;
		$o->coupon_type = $coupon;
		$o->value = $value ?: $coupon->value;
		if ($ticket)
			$o->ticket = $ticket;
		$o->save();
		return $o;
	}
	
	public function isFixed() {
		return $this->coupon_type->isFixed();
	}
	
	public function isMultiuse() {
		return $this->coupon_type->isMultiuse();
	}
	
	public function alreadyUsesMultiuse(Model_Ticket $ticket) {
		return (new Model_Coupon())
				->where('coupon_type_id', '=', $this->coupon_type_id)
				->where('ticket_id','=',$ticket->pk())->count_all() > 0;
	}
	
	/**
	 * Consume this coupon for a discount on the specified ticket.
	 * The ticket provided will have its price reduced by the relevant value.
	 * Note: this method asssumes that all consumptions of multiple coupons happen
	 * in a database transaction.
	 * @param Model_Ticket $ticket Ticket that is consuming this coupon
	 */
	public function consume(Model_Ticket $ticket) {
		if ($this->ticket_id) // sanity - we already have a ticket
			throw new Exception("Trying to double consume coupon " . $this->pk() . " for ticket " . $ticket->pk() . "!");
		
		if ($this->isMultiuse()) {
			// multi use coupons mean we generate a duplicate and list it as being used, while
			// the main coupon is free to be used again - but not by the same ticket, so we check for that.
			if ($this->alreadyUsesMultiuse($ticket))
				return; // duplicate use is not allowed
			
			// safe to use
			Model_Coupon::persist($this->coupon_type, $this->user, null, $ticket);
			if ($this->isFixed()) {
				$ticket->price -= $this->value;
				if($ticket->price < 0) $ticket->price=0;
			} else {
				$ticket->price -= $ticket->price * $this->value / 100; // "value" says "percent discount"
			}
			$ticket->save();
			return;
		}
		
		// one use coupons are simply consumed
		if ($this->value > $ticket->price) {
			Model_Coupon::persist($this->coupon_type, $this->user, $ticket->price, $ticket);
			$this->value -= $ticket->price;
			$ticket->price = 0;
			$this->save();
		} else {
			$ticket->price -= $this->value;
			$this->value = 0;
			$this->ticket = $ticket;
			$this->save();
		}
		$ticket->save();
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
		
		$this->ticket = null;
		$this->save();
	}
	
	public function for_json() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return in_array($key, [ 'id', 'value' ]);
		},ARRAY_FILTER_USE_KEY), [
				'user' => $this->user->for_json(),
				'type' => $this->coupon_type->for_json(),
				'ticket' => $this->ticket_id ? $this->ticket->for_json : null,
				'convention' => $this->coupon_type->convention->for_json(),
		]);
	}
	
};
