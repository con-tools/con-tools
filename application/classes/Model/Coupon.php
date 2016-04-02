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
	 * The ticket provided will have its price reduced by the relevant amount.
	 * Note: this method asssumes that all consumptions of multiple coupons happen
	 * in a database transaction.
	 * @param Model_Ticket $ticket Ticket that is consuming this coupon
	 */
	public function consume(Model_Ticket $ticket) {
		if ($this->ticket) // sanity - we already have a ticket
			throw new Exception("Trying to double consume coupon " . $this->pk() . " for ticket " . $ticket->pk() . "!");
		
		if ($this->isMultiuse()) {
			// multi use coupons mean we generate a duplicate and list it as being used, while
			// the main coupon is free to be used again - but not by the same ticket, so we check for that.
			if ($this->alreadyUsesMultiuse($ticket))
				return; // duplicate use is not allowed
			
			// safe to use
			$new = Model_Coupon::persist($this->coupon_type, $this->user);
			$new->setTicket($ticket);
			if ($this->isFixed()) {
				$ticket->price -= $this->amount;
			} else {
				$ticket->price -= $ticket->price * $this->amount / 100; // "amount" says "percent discount"
			}
			$ticket->save();
			return;
		}
		
		// one use coupons are simply consumed
		$this->amount -= $ticket->price;
		if ($this->amount > 0) {
			// ha ha, too big for you puny ticket!
			$ticket->price = 0;
			$ticket->save;
			$this->setTicket($ticket);
			return;
		}
		
		// I'm not strong enough!
		
		$ticket->price = -1 * $this->amount; // leave the rest of the money that we didn't discount on the ticket
		$ticket->save();
		$this->amount = 0;
		$this->setTicket($ticket);
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
	
	public function setTicket(Model_Ticket $ticket) : Model_Ticket {
		$this->ticket = $ticket;
		return $this->save();
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
