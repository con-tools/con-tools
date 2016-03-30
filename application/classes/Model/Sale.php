<?php

class Model_Sale extends ORM {
	
	protected $_belongs_to = [
			'convention' => [],
			'user' => [],
			'cashier' => [ 'model' => 'user', 'foreign_key' => 'cashier_id' ],
			'sale' => [ 'model' => 'sale', 'foreign_key' => 'original_sale_id' ],
	];
	
	protected $_has_many = [
			'tickets' => [],
			'coupons' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'convention_id' => [],
			'user_id' => [],
			'cashier_id' => [],
			'original_sale_id' => [], // if not null, this is a cancellation transaction,
			// and transaction_id is the cancellation confirmation. refer to original sale for actual transaction ID
			// data fields
			'transaction_id' => [],
			'sale_time' => [ 'type' => 'DateTime' ],
			'cancellation_notes' => [],
			'processor_data' => [], // processor specific transaction meta-data. The payment process can use this to store temp data
	];

	/**
	 * Generate a new sale for this convention goer, for everything in their shopping card
	 * @param Model_User $user Convention Goer
	 * @param Model_Convention $con Convention they go to
	 * @param Model_User $cashier (optional) cashier that sold them the tickets
	 * @return Model_Sale
	 */
	public static function persist(Model_User $user, Model_Convention $con, Model_User $cashier = null) : Model_Sale {
		$o = new Model_Sale();
		$o->convention = $con;
		$o->user = $user;
		$o->cashier = $cashier;
		$o->sale_time = new DateTime();
		$o->save();
		foreach (Model_Ticket::shoppingCart($con, $user) as $ticket) {
			$ticket->setSale($o);
		}
		return $o;
	}

	/**
	 * Given an arbitrary collection of tickets (and in the future also coupons), figure out the
	 * shopping cart cost.
	 * @param array|Database_Result $tickets list or result set of Model_Ticket
	 */
	public static function computeTotal($tickets) {
		return array_reduce(is_array($tickets) ? $tickets : $tickets->as_array(), function(int $total, Model_Ticket $ticket){
			return $total + $ticket->price;
		}, 0);
	}
	
	/**
	 * Get total cost of this sale
	 */
	public function getTotal() {
		return self::computeTotal($this->tickets->find_all());
	}
	
	/**
	 * Finished transaction
	 * @param string $transaction_id payment processor transaction id
	 */
	public function authorized($transaction_id) {
		$this->transaction_id = $transaction_id;
		$this->save();
		foreach ($this->tickets->find_all() as $ticket)
			$ticket->authorize();
	}
	
	/**
	 * User cancelled the transaction, return all tickets to "reserved"
	 * so they can try again later
	 */
	public function cancelled() {
		$this->failed("internal:user-cancelled");
	}
	
	/**
	 * Payment processor failed the transaction, return all tickets to "reserved"
	 * so they can try again later
	 */
	public function failed($reasonCode) {
		foreach ($this->tickets->find_all() as $ticket)
			$ticket->returnToCart();
		$this->transaction_id("FAILED:" . $reasonCode);
	}
};
