<?php

class Controller_Entities_Purchases extends Api_Rest_Controller {
	
	public function create() {
		$data = $this->input();
		Logger::debug("Loading SKU '".$data->sku."' for purchase creation");
		try {
			$sku = Model_Merchandise_Sku::byCodeOrId($data->sku);
		} catch (Model_Exception_NotFound $e) {
			throw new Api_Exception_InvalidInput($this, "No merchandise SKU specified for the sale");
		}
		$amount = $data->amount ?: 1;
		return Model_Purchase::persist($sku, $this->getValidUser(), $amount)->for_json();
	}
	
	public function retrieve($id) {
		$purchase = new Model_Purchase($id);
		if ($purchase->loaded() && ($purchase->user == $this->getValidUser() || $this->systemAccessAllowed()))
			return $purchase->for_json();
		throw new Api_Exception_InvalidInput($this, "No valid purchases found to display");
	}
	
	public function update($id) {
		// allow user to update the amount of merchandise they want to buy
		$amount = $this->input()->amount;
		if (!is_numeric($amount))
			throw new Api_Exception_InvalidInput($this, "Amount must be a numerical value");
		
		// allow admins to load and update any purchase, as long as they know the ID
		$purchase = null;
		if ($this->systemAccessAllowed()) {
			$purchase = new Model_Purchase($id);
			if (!$purchase->loaded())
				$purchase = null; // zero out, so we can try again
		}
		
		// users get access through a loader that checks that they own the purchase and also understand sku codes
		if (!$purchase) {
			try {
				$purchase = Model_Purchase::byIdOrSkuCode($this->getValidUser(), $id);
			} catch (Model_Exception_NotFound $e) {
				throw new Api_Exception_InvalidInput($this, "No purchases found");
			}
		}
		Logger::debug("Setting purchase amount of (" . $this->user->email . "): " .$purchase->sku->code." to " . $amount);
		$purchase->setAmount($amount);
		
		if ($purchase->amount <= 0) { // cancel the purchase
			$purchase->cancel("user-deleted");
			return $purchase->for_json();
		}

		return $purchase->save()->for_json();
	}
	
	public function delete($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not authorized to delete purchases");
		$purchase = new Model_Purchase((int)$id);
		if (!$purchase->loaded())
			throw new Api_Exception_InvalidInput($this, "No purchase found for '$id'");
		if ($purchase->isAuthorized())
			throw new Api_Exception_InvalidInput($this, "Can't delete authorized purchases, cancel it first");
		$purchase->delete();
		return true;
	}
	
	public function catalog() {
		$data = $this->input();
		// two different base modes - user and admin/convention
		$filters = [];
		if ($data->all and $this->systemAccessAllowed()) {
			// ehmm.. no default filters, unless the caller asked for a user filter
			if ($data->by_user) {
				if (is_numeric($data->by_user))
					$filters['users.user_id'] = $data->by_user;
				else
					$filters['email'] = $data->by_user;
			}
		} else {
			// verify user and filter by them
			$filters['purchase.user_id'] = $this->getValidUser()->pk();
		}
		
		if ($data->by_sku)
			$filters['sku.code'] = $data->by_sku;
		if ($data->is_valid)
			$filters['valid'] = 1;
		return ORM::result_for_json($this->convention->getPurchases($filters));
	}
	
	private function getValidUser() {
		if ($this->user) // user authenticated themselves - fine
			return $this->user;
		if ($this->convention->isAuthorized() and $this->input()->user)
			return $this->loadUserByIdOrEmail($this->input()->user, $this->input()->user);
		throw new Api_Exception_InvalidInput($this, "User must be authenticated or specified by an authorized convention");
	}
	
}
