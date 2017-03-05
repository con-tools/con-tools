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
			'user_passes' => [],
			'purchases' => [ 'model' => 'Purchase' ],
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
	 * @param Model_User $cashier (optional) cashier that sold them the tickets or passes
	 * @return Model_Sale
	 */
	public static function persist(Model_User $user, Model_Convention $con, Model_User $cashier = null) : Model_Sale {
		$o = new Model_Sale();
		$o->convention = $con;
		$o->user = $user;
		$o->cashier = $cashier;
		$o->sale_time = new DateTime();
		$o->save();
		foreach (Model_Sale_Item::shoppingCart($con, $user) as $item) {
			$item->setSale($o);
		}
		foreach (Model_Purchase::shoppingCart($con, $user) as $purchase) {
			$purchase->setSale($o);
		}
		return $o;
	}

	/**
	 * Given an arbitrary collection of tickets/passes and/or purchases, figure out the shopping cart cost.
	 * @param array|Database_Result $items list or result set of Model_Ticket or Model_Purchase
	 */
	public static function computeTotal($items) {
		return array_reduce(is_array($items) ? $items : $items->as_array(), function(int $total, ORM $item){
			return $total + $item->price;
		}, 0);
	}
	
	public function get($column) {
		switch ($column) {
			case 'processor_data':
				return json_decode(parent::get('processor_data'), true);
			default: return parent::get($column);
		}
	}
	
	public function set($column, $value) {
		switch ($column) {
			case 'processor_data':
				return parent::set('processor_data', json_encode($value));
			default: return parent::set($column, $value);
		}
	}
	
	/**
	 * Get total cost of this sale
	 */
	public function getTotal() {
		return self::computeTotal($this->tickets->find_all()) +
			self::computeTotal($this->user_passes->find_all()) +
			self::computeTotal($this->purchases->find_all());
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
		foreach ($this->user_passes->find_all() as $pass)
			$pass->authorize();
		foreach ($this->purchases->find_all() as $purchase)
			$purchase->authorize();
	}
	
	/**
	 * User cancelled the transaction, return all tickets/passes to "reserved"
	 * so they can try again later
	 */
	public function cancelled() {
		$this->failed("internal:user-cancelled");
	}
	
	/**
	 * Payment processor failed the transaction, return all tickets/passes to "reserved"
	 * so they can try again later
	 */
	public function failed($reasonCode) {
		foreach ($this->tickets->find_all() as $ticket)
			$ticket->returnToCart();
		foreach ($this->user_passes->find_all() as $pass)
			$pass->returnToCart();
		foreach ($this->purchases->find_all() as $purchase)
			$purchase->returnToCart();
		$this->transaction_id = "FAILED:" . $reasonCode;
	}
	
	public function failReason() {
		return str_replace("FAILED:","", $this->transaction_id);
	}
	
	public function for_json() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return in_array($key, [
					'id', 'transaction-id', 'sale-time', 'cancellation-notes', 'processor-data',
			]);
		},ARRAY_FILTER_USE_KEY),[
				'user' => $this->user->for_json(),
				'cashier' => $this->cashier_id ? $this->cashier->for_json() : null,
		]);
	
	}
	
};
