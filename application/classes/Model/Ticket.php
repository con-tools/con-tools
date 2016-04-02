<?php

class Model_Ticket extends ORM {
	
	const STATUS_RESERVED = 'reserved';
	const sTATUS_PROCESSING = 'processing';
	const STATUS_AUTHORIZED = 'authorized';
	const STATUS_CANCELLED = 'cancelled';
	
	protected $_belongs_to = [
			'user' => [],
			'timeslot' => [],
			'sale' => [],
	];
	
	protected $_has_many = [
			'coupons' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'user_id' => [],
			'timeslot_id' => [],
			'sale_id' => [],
			// data fields
			'amount' => [], // number of tickets
			'price' => [], // fullfilment price for the entire model (i.e when amount > 1, for all the amount)
			'status' => [ 'type' => 'enum', 'values' => [ 'reserved', 'processing', 'authorized', 'cancelled' ]],
			'reserved_time' => [ 'type' => 'DateTime' ],
			'cancel_reason' => [],
	];
	
	public static function persist(Model_Timeslot $timeslot, Model_User $user, int $amount = 1, $price = null) : Model_Ticket {
		$o = new Model_Ticket();
		$o->user = $user;
		$o->timeslot = $timeslot;
		$o->status = self::STATUS_RESERVED;
		$o->reserved_time = new DateTime();
		$o->amount = $amount;
		$o->price = $price ?: ($o->amount * $o->timeslot->event->price);
		$o->save();
		$o->consumeCoupons(); // see if there are any coupons that apply to these tickets
		return $o;
	}
	
	/**
	 * Count the number of tickets locked (reserved, in process or sold) for a time slot
	 * @param Model_Timeslot $timeslot
	 * @return int number of tickets
	 */
	public static function countForTimeslot(Model_Timeslot $timeslot) : int {
		// no caching, always return all tickets as of now
		return DB::select([DB::expr('SUM(`amount`)'), 'total_tickets'])->
				from((new Model_Ticket())->table_name())->
				where('timeslot_id', '=', $timeslot->pk())->
				where('status','<>', self::STATUS_CANCELLED)->
				execute()->get('total_tickets') ?: 0;
	}
	
	public static function queryForConvention(Model_Convention $con) : ORM {
		$query = (new Model_Ticket())->with('timeslot:event')->with('user')->where('convention_id', '=', $con->pk());
		return $query;
	}
	
	/**
	 * Retrieve the ticket shopping card for the user
	 * @param Model_Convention $con Convention where the user goes
	 * @param Model_User $user User that goes to a convention
	 */
	public static function shoppingCart(Model_Convention $con, Model_User $user) : Database_Result {
		return (new Model_Ticket())->
				with('timeslot:event')->
				with('user')->
				where('convention_id', '=', $con->pk())->
				where('ticket.user_id','=',$user->pk())->
				where('ticket.status', 'IN', [ self::STATUS_RESERVED, self::sTATUS_PROCESSING ])->
				find_all();
	}
	
	public static function oldTickets(DateTime $than) {
		return (new Model_Ticket())->
			with('timeslot:event')->
		with('user')->
		where('convention_id', '=', $con->pk())->
		where('ticket.user_id','=',$user->pk())->
		where('ticket.status', 'IN', [ self::STATUS_RESERVED, self::sTATUS_PROCESSING ])->
		find_all();
	}
	
	public function consumeCoupons() {
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
			case 'convention':
				return $this->timeslot->event->convention;
			default:
				return parent::get($column);
		}
	}
	
	public function setSale(Model_Sale $sale) {
		$this->sale = $sale;
		$this->status = self::sTATUS_PROCESSING;
		return $this->save();
	}
	
	/**
	 * Update the amount of tickets purchased for the time slot, and re-consume coupons
	 * @param int $amount
	 */
	public function setAmount(int $amount) {
		// update amount and price
		$this->amount = $amount < 0 ? 0 : $amount;
		$this->price = $this->timeslot->event->price * $this->amount;
		// return all coupons
		foreach ($this->coupons->find_all() as $coupon) {
			$coupon->release();
		}
		$this->consumeCoupons();
	}
	
	public function cancel($reason) : Model_Ticket {
		$this->status = self::STATUS_CANCELLED;
		$this->cancel_reason = $reason;
		return $this->save();
	}
	
	public function authorize() {
		$this->status = self::STATUS_AUTHORIZED;
		return $this->save();
	}
	
	public function returnToCart() {
		$this->status = self::STATUS_RESERVED;
		$this->reserved_time = new DateTime(); // give the user a bit more time
		$this->save();
	}
	
	public function isAuthorized() {
		return $this->status == self::STATUS_AUTHORIZED;
	}
	
	public function for_json_with_coupons() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return in_array($key, [
					'id', 'status', 'amount', 'sale-id', 'price'
			]);
		},ARRAY_FILTER_USE_KEY),[
				'timeslot' => $this->timeslot->for_json(),
				'user' => $this->user->for_json(),
				'coupons' => self::result_for_json($this->coupons->find_all()),
		]);
		
	}
	
	public function for_json() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return in_array($key, [
					'id', 'status', 'amount', 'sale-id', 'price'
			]);
		},ARRAY_FILTER_USE_KEY),[
				'timeslot' => $this->timeslot->for_json(),
				'user' => $this->user->for_json(),
		]);
		
	}
	
}
