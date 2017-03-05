<?php

abstract class Model_Sale_Item extends ORM {
	
	const STATUS_RESERVED = 'reserved';
	const STATUS_PROCESSING = 'processing';
	const STATUS_AUTHORIZED = 'authorized';
	const STATUS_CANCELLED = 'cancelled';
	const STATUS_REFUNDED = 'refunded';

	public static function validStatuses() {
		return [
				self::STATUS_RESERVED,
				self::STATUS_PROCESSING,
				self::STATUS_AUTHORIZED,
		];
	}
	
	/**
	 * Implement this in method to generate the value for the 'object_type' field in referencing models
	 * @return object_type value to use for this instance, such as 'ticket' or 'user_pass'
	 */
	abstract public function getTypeName();
	
	/**
	 * compute price of this item from the base data - used when returning coupons
	 */
	abstract public function computePrice();

	public function consumeCoupons() {
		if ($this->price <= 0)
			return $this->save(); // no need to consume coupons
		
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
			case 'coupons':
				return parent::get($column)->where('object_type', '=', $this->getTypeName());
			default:
				return parent::get($column);
		}
	}
	
	public function isAuthorized() {
		return $this->status == self::STATUS_AUTHORIZED || ($this->sale_id and $this->sale->transaction_id);
	}
	
	public function isCancelled() {
		return $this->status == self::STATUS_CANCELLED;
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
		$this->price = $this->computePrice();
	}
	
	public function setSale(Model_Sale $sale) {
		$this->sale = $sale;
		$this->status = self::STATUS_PROCESSING;
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
	
	/**
	 * Cancel a sale item that has not been payed for yet.
	 * This will return all coupons used in the sale item.
	 * @param string $reason Reason for the cancellation
	 * @throws Exception in case the sale item has already been payed for
	 * @return Model_Sale_Item the sale item itself
	 */
	public function cancel($reason) : Model_Sale_Item {
		if ($this->status == self::STATUS_AUTHORIZED)
			throw new Exception("An authorized ".$this->getTypeName() ." cannot be cancelled!");
		$this->status = self::STATUS_CANCELLED;
		$this->cancel_reason = $reason;
		$this->returnCoupons();
		return $this->save();
	}
	
	/**
	 * Refund an already purchased sale item by returning all coupons and creating a refund coupon for the payed amount
	 * @param Model_Coupon_Type $refundType The coupon type to create for refunded amount
	 * @param string $reason Reason for the refund
	 * @throws Exception in case the ticket has not been payed for yet
	 * @return Model_Sale_Item the sale item itself
	 */
	public function refund(Model_Coupon_Type $refundType, $reason) : Model_Sale_Item {
		if ($this->status != self::STATUS_AUTHORIZED)
			throw new Exception("Cannot refund a ticket that has not been payed for yet");
		$refundAmount = $this->price;
		$this->returnCoupons();
		// reset amount after "return coupons" to show how much the user has actually paid - this is important for consolidation
		$this->price = $refundAmount;
		$this->status = self::STATUS_REFUNDED;
		$this->cancel_reason = $reason;
		if ($refundAmount > 0)
			Model_Coupon::persist($refundType, $this->user, "Refund for ".$this->getTypeName().":" . $this->pk(), $refundAmount);
		return $this->save();
	}
	
}
