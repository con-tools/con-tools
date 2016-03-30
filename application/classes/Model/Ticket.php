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
	];
	
	public static function persist(Model_Timeslot $timeslot, Model_User $user, int $amount = 1, $price = null) : Model_Ticket {
		$o = new Model_Ticket();
		$o->user = $user;
		$o->timeslot = $timeslot;
		$o->status = self::STATUS_RESERVED;
		$o->amount = $amount;
		$o->price = $price ?: ($o->amount * $o->timeslot->event->price);
		return $o->save();
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
				where('ticket.status', '=', self::STATUS_RESERVED)->
				find_all();
	}
	
	public function setSale(Model_Sale $sale) {
		$o->sale = $sale;
		$o->status = self::sTATUS_PROCESSING;
		$o->save();
	}
	
	public function isAuthorized() {
		return $this->status == self::STATUS_AUTHORIZED;
	}
	
	public function for_json() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return in_array($key, [
					'id', 'status', 'amount', 'sale-id', 'timeslot', 'user', 'price'
			]);
		},ARRAY_FILTER_USE_KEY),[
		]);
		
	}
	
}
