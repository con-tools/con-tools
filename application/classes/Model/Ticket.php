<?php

class Model_Ticket extends Model_Sale_Item {
	
	protected $_belongs_to = [
			'user' => [],
			'timeslot' => [],
			'sale' => [],
			'user_pass' => [], // when using passes, we still use tickets for registration, but we link them to the pass in use and we don't sell them
	];
	
	protected $_has_many = [
			'coupons' => [ 'foreign_key' => 'object_id' ],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'user_id' => [],
			'timeslot_id' => [],
			'sale_id' => [],
			'user_pass_id' => [],
			// data fields
			'amount' => [], // number of tickets
			'price' => [], // fullfilment price for the entire model (i.e when amount > 1, for all the amount)
			'status' => [ 'type' => 'enum', 'values' => [ 'reserved', 'processing', 'authorized', 'cancelled', 'refunded' ]],
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
	 * Create and store a ticket registering the user of a user pass
	 * @param Model_Timeslot $timeslot time slot on which to register the user
	 * @param Model_User_Pass $pass User pass to register with
	 * @return Model_Ticket the ticket that was generated for this registration
	 */
	public static function forPass(Model_Timeslot $timeslot, Model_User_Pass $pass) : Model_Ticket {
		$o = new Model_Ticket();
		$o->user = $pass->user;
		$o->timeslot = $timeslot;
		$o->user_pass = $pass;
		$o->status = $pass->status; // use the pass status - if the user pass is not authorized, nor is this ticket
		$o->reserved_time = new DateTime();
		$o->amount = 1; // only one ticket per pass
		$o->price = 0; // user has paid for the pass, so they don't need to pay here
		$o->save();
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
				where('status','IN', self::validStatuses())->
				execute()->get('total_tickets') ?: 0;
	}
	
	public static function queryForConvention(Model_Convention $con) : ORM {
		$query = (new Model_Ticket())->with('timeslot:event')->with('user')->where('convention_id', '=', $con->pk());
		return $query;
	}
	
	public static function reservedByReserveTime(DateTime $latest) : Database_Result {
		return (new Model_Ticket())
				->where('status','=', self::STATUS_RESERVED)
				->where('reserved_time', '<', $latest->format('Y-m-d H:i:s'))
				->find_all();
	}
	
	public static function processingByReserveTime(DateTime $latest) : Database_Result {
		return (new Model_Ticket())
				->where('status','=', self::STATUS_PROCESSING)
				->where('reserved_time', '<', $latest->format('Y-m-d H:i:s'))
				->find_all();
	}
	
	/**
	 * Retrieve the ticket shopping cart for the user
	 * @param Model_Convention $con Convention where the user goes
	 * @param Model_User $user User that goes to a convention
	 */
	public static function shoppingCart(Model_Convention $con, Model_User $user) : Database_Result {
		return (new Model_Ticket())->
				with('timeslot:event')->
				with('user')->
				where('convention_id', '=', $con->pk())->
				where('ticket.user_id','=',$user->pk())->
				where('ticket.status', 'IN', [ self::STATUS_RESERVED, self::STATUS_PROCESSING ])->
				find_all();
	}
	
	/**
	 * Retrieve all tickets for the user in the convention, regardless of status.
	 * Compare with {@link Model_Ticket#shoppingCart()}
	 * @param Model_Convention $con
	 * @param Model_User $user
	 * @return Database_Result
	 */
	public static function byConventionUSer(Model_Convention $con, Model_User $user) : Database_Result {
		return (new Model_Ticket())->
				with('timeslot:event')->
				with('user')->
				where('convention_id', '=', $con->pk())->
				where('ticket.user_id','=',$user->pk())->
				find_all();
	}
	
	public static function oldTickets(DateTime $than) {
		return (new Model_Ticket())->
			with('timeslot:event')->
			with('user')->
			where('convention_id', '=', $con->pk())->
			where('ticket.user_id','=',$user->pk())->
			where('ticket.status', 'IN', [ self::STATUS_RESERVED, self::STATUS_PROCESSING ])->
			find_all();
	}
	
	public function getTypeName() {
		return 'ticket';
	}
	
	public function get($column) {
		switch($column) {
			case 'convention':
				return $this->timeslot->event->convention;
			default:
				return parent::get($column);
		}
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
	
	public function computePrice() {
		// recompute price, so we'll see how much that ticket would have cost without coupons
		return $this->timeslot->event->price * $this->amount;
	}
	
	public function for_json_with_coupons() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return in_array($key, [
					'id', 'status', 'amount', 'price', 'reserved-time',
			]);
		},ARRAY_FILTER_USE_KEY),[
				'timeslot' => $this->timeslot->for_json(),
				'user' => $this->user->for_json(),
				'coupons' => self::result_for_json($this->coupons->find_all()),
				'sale' => $this->sale_id ? $this->sale->for_json() : null,
		]);
	}
	
	public function for_json() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return in_array($key, [
					'id', 'status', 'amount', 'price', 'reserved-time',
			]);
		},ARRAY_FILTER_USE_KEY),[
				'timeslot' => $this->timeslot->for_json(),
				'user' => $this->user->for_json(),
				'sale' => $this->sale_id ? $this->sale->for_json() : null,
		]);
		
	}
	
}
