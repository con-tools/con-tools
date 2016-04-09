<?php

class Model_Purchase extends ORM {
	
	const STATUS_RESERVED = 'reserved';
	const STATUS_PROCESSING = 'processing';
	const STATUS_AUTHORIZED = 'authorized';
	const STATUS_CANCELLED = 'cancelled';
	
	protected $_belongs_to = [
			'user' => [],
			'sku' => [],
			'sale' => [],
	];
	
	protected $_has_many = [
			//'coupons' => [], // maybe tomorrow
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'user_id' => [],
			'sku_id' => [],
			'sale_id' => [],
			// data fields
			'amount' => [], // number of items per SKU
			'price' => [], // fullfilment price for the entire model (i.e when amount > 1, for all the amount)
			'status' => [ 'type' => 'enum', 'values' => [ 'reserved', 'processing', 'authorized', 'cancelled' ]],
			'reserved_time' => [ 'type' => 'DateTime' ],
			'cancel_reason' => [],
	];
	
	public static function persist(Model_Merchandise_Sku $sku, Model_User $user, int $amount = 1, $price = null) : Model_Purchase {
		$o = new Model_Purchase();
		$o->user = $user;
		$o->sku = $sku;
		$o->status = self::STATUS_RESERVED;
		$o->reserved_time = new DateTime();
		$o->amount = $amount;
		$o->price = $price ?: ($o->amount * $o->sku->price);
		$o->save();
		// $o->consumeCoupons(); // coupons cannot be used for merchandise according to Bigor 16 (may change in the future)
		return $o;
	}
	
	public static function queryForConvention(Model_Convention $con) : ORM {
		$query = (new Model_Purchase())->with('merchandise_sku')->with('user')->where('convention_id', '=', $con->pk());
		return $query;
	}
	
	/**
	 * Retrieve the merchandise shopping cart for the user
	 * @param Model_Convention $con Convention where the user goes
	 * @param Model_User $user User that goes to a convention
	 */
	public static function shoppingCart(Model_Convention $con, Model_User $user) : Database_Result {
		return (new Model_Purchase())->
				with('merchandise_sku')->
				with('user')->
				where('convention_id', '=', $con->pk())->
				where('purchase.user_id','=',$user->pk())->
				where('purchase.status', 'IN', [ self::STATUS_RESERVED, self::STATUS_PROCESSING ])->
				find_all();
	}
	
	public function get($column) {
		switch($column) {
			case 'convention':
				return $this->sku->convention;
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
	 * Update the amount of merchandise purchased
	 * @param int $amount
	 */
	public function setAmount(int $amount) {
		// update amount and price
		$this->amount = $amount < 0 ? 0 : $amount;
		$this->price = ($this->amount * $this->sku->price);
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
		$this->save();
	}
	
	public function isAuthorized() {
		return $this->status == self::STATUS_AUTHORIZED;
	}
	
	public function for_json() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return !in_array($key, [ 'user-id', 'sku-id' ]);
		},ARRAY_FILTER_USE_KEY),[
				'sku' => $this->sku->for_json(),
				'user' => $this->user->for_json(),
		]);
		
	}

}
