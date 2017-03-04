<?php

class Model_Ticket extends Model_Sale_Item {
	
	protected $_belongs_to = [
			'user' => [],
			'timeslot' => [],
			'sale' => [],
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
	
	public function setSale(Model_Sale $sale) {
		$this->sale = $sale;
		$this->status = self::STATUS_PROCESSING;
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
	
	/**
	 * Cancel a ticket that has not been payed for yet.
	 * This will return all coupons used in the ticket.
	 * @param string $reason Reason for the cancellation
	 * @throws Exception in case the ticket has already been payed for
	 * @return Model_Ticket the ticket itself
	 */
	public function cancel($reason) : Model_Ticket {
		if ($this->status == self::STATUS_AUTHORIZED)
			throw new Exception("An authorized ticket cannot be cancelled!");
		$this->status = self::STATUS_CANCELLED;
		$this->cancel_reason = $reason;
		$this->returnCoupons();
		return $this->save();
	}
	
	/**
	 * Refund an already purchased ticket by returning all coupons
	 * and creating a refund coupon for the payed amount
	 * @param Model_Coupon_Type $refundType The coupon type to create for refunded amount
	 * @param string $reason Reason for the refund
	 * @throws Exception in case the ticket has not been payed for yet
	 * @return Model_Ticket the ticket itself
	 */
	public function refund(Model_Coupon_Type $refundType, $reason) : Model_Ticket {
		if ($this->status != self::STATUS_AUTHORIZED)
			throw new Exception("Cannot refund a ticket that has not been payed for yet");
		$refundAmount = $this->price;
		$this->returnCoupons();
		// reset amount after "return coupons" to show how much the user has actually paid - this is important for consolidation
		$this->price = $refundAmount;
		$this->status = self::STATUS_REFUNDED;
		$this->cancel_reason = $reason;
		if ($refundAmount > 0)
			Model_Coupon::persist($refundType, $this->user, "Refund for ticket:" . $this->pk(), $refundAmount);
		return $this->save();
	}
	
	/**
	 * Return all coupons used for this ticket, and recalculate non-couponed price
	 * This method does not save the object, as it is expected to be used as part
	 * of a larger transaction
	 */
	public function returnCoupons() {
		foreach ($this->coupons->find_all() as $coupon) {
			$coupon->release();
		}
		// recompute price, so we'll see how much that ticket would have cost without coupons
		$this->price = $this->timeslot->event->price * $this->amount;
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
		return $this->status == self::STATUS_AUTHORIZED || ($this->sale_id and $this->sale->transaction_id);
	}

	public function isCancelled() {
		return $this->status == self::STATUS_CANCELLED;
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
