<?php

class Model_User_Pass extends Model_Sale_Item {
	
	protected $_belongs_to = [
			'user' => [],
			'pass' => [],
			'sale' => [],
	];
	
	protected $_has_many = [
			'coupons' => [ 'foreign_key' => 'object_id' ],
			'tickets' => [],
	];
	
	protected $_columsn = [
			'id' => [],
			// foreign keys
			'user_id' => [],
			'pass_id' => [],
			// data fields
			'name' => [],
			'price' => [], // fulfillment price
			'status' => [ 'type' => 'enum', 'values' => [ 'reserved', 'processing', 'authorized', 'cancelled', 'refunded' ]],
			'reserved_time' => [ 'type' => 'DateTime' ],
			'cancel_reason' => [],
	];
	
	/**
	 * Store a new user pass record
	 * @param Model_User $user User that owns the pass
	 * @param Model_Pass $pass The pass being owned
	 * @param string $name The visitor name to print on the pass
	 * @param string|float $price the cost assigned to this pass purchase (may be null, in which case
	 *   the cost will be generated from the pass price
	 * @return Model_User_Pass pass ownership record created
	 */
	public static function persist(Model_User $user, Model_Pass $pass, $name, $price) : Model_User_Pass {
		$o = new Model_User_Pass();
		$o->user = $user;
		$o->pass = $pass;
		$o->name = $name;
		$o->price = $price ?: $pass->price;
		$o->reserved_time = new DateTime();
		$o->status = self::STATUS_RESERVED;
		$o->save();
		$o->consumeCoupons(); // see if there are any coupons that apply to these passes
		return $o;
	}
	
	public static function queryForConvention(Model_Convention $con) : ORM {
		return (new Model_User_Pass())->with('pass')->with('user')->where('convention_id', '=', $con->pk());
	}
	
	public static function reservedByReserveTime(DateTime $latest) : Database_Result {
		return (new Model_User_Pass())
			->where('status','=', self::STATUS_RESERVED)
			->where('reserved_time', '<', $latest->format('Y-m-d H:i:s'))
			->find_all();
	}
	
	public static function processingByReserveTime(DateTime $latest) : Database_Result {
		return (new Model_User_Pass())
			->where('status','=', self::STATUS_PROCESSING)
			->where('reserved_time', '<', $latest->format('Y-m-d H:i:s'))
			->find_all();
	}
	
	public function getTypeName() {
		return 'user_pass';
	}
	
	public function computePrice() {
		// recompute price, so we'll see how much that pass would have cost without coupons
		return $this->pass->price;
	}
	
	/**
	 * Special authorize processing for passes - authorize all tickets associated with this pass
	 * {@inheritDoc}
	 * @see Model_Sale_Item::authorize()
	 */
	public function authorize() {
		parent::authorize();
		foreach ($this->tickets->find_all() as $ticket) {
			$ticket->authorize();
		}
	}
	
	/**
	 * Special cancel processing for passes - cancel all tickets associated with this pass
	 * {@inheritDoc}
	 * @see Model_Sale_Item::cancel()
	 */
	public function cancel($reason) {
		parent::cancel($reason);
		foreach ($this->tickets->find_all() as $ticket) {
			$ticket->cancel($reason);
		}
	}
	
	public function get($column) {
		switch ($column) {
			case 'convention':
				return $this->pass->convention;
			default:
				return parent::get($column);
		}
	}
	
	/**
	 * Check if this user pass has no booking between the specified times
	 * @param DataTime $start Start time to compare
	 * @param DateTime $end end time to compare
	 * @return boolean whether the pass is available for booking at the specified times
	 */
	public function availableDuring(DataTime $start, DateTime $end) {
		foreach ($this->tickets->find_all() as $ticket) {
			$timeslot = $ticket->timeslot;
			if ($timeslot->conflicts($start, $end))
				return false;
		}
		return true;
	}

// 	public function for_json() {
// 		return array_merge(array_filter(parent::for_json(),function($key){
// 			return in_array($key, [
// 					'id', 'name',
// 			]);
// 		},ARRAY_FILTER_USE_KEY),[
// 				'user' => $this->user->for_json(),
// 				'pass' => $this->pass->format(DateTime::ATOM),
// 		]);
// 	}

	public function for_json_with_coupons() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return in_array($key, [
					'id', 'status', 'name', 'price', 'reserved-time',
			]);
		},ARRAY_FILTER_USE_KEY),[
				'user' => $this->user->for_json(),
				'pass' => $this->pass->for_json(),
				'coupons' => self::result_for_json($this->coupons->find_all()),
				'sale' => $this->sale_id ? $this->sale->for_json() : null,
		]);
		
	}
}
