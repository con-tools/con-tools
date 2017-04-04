<?php

class Controller_Entities_Userpasses extends Api_Rest_Controller {
	
	protected function create() {
		$data = $this->input();
		$pass = new Model_Pass($data->pass);
		if (!$pass->loaded())
			throw new Api_Exception_InvalidInput($this, "No time pass specified for the sell");
		if (!$data->name)
			throw new Api_Exception_InvalidInput($this, "No visitor name specified for the pass");
		$price = null;
		if ($this->systemAccessAllowed() && $data->price)
			$price = $data->price;
		// start the reservation
		$userpass = Model_User_Pass::persist($this->getValidUser(), $pass, $data->name, $price);
		return $userpass->for_json();
	}
	
	protected function retrieve($id) {
		$pass = new Model_User_Pass($id);
		if ($pass->loaded() && ($pass->user == $this->getValidUser() || $this->systemAccessAllowed()))
			return $pass->for_json_with_coupons();
		throw new Api_Exception_InvalidInput($this, "No valid passes found to display");
	}
	
	protected function update($id) {
		// allow user to update the name on the ticket
		$name = $this->input()->name;
		if (!$name)
			throw new Api_Exception_InvalidInput($this, "Only name can be updated");
		$pass = new Model_User_Pass((int)$id);
		if (!$pass->loaded() and (!$this->systemAccessAllowed() and $ticket->user != $this->getValidUser()))
			throw new Api_Exception_InvalidInput($this, "No valid pass found");
		if ($pass->isAuthorized() or $pass->isCancelled())
			throw new Api_Exception_InvalidInput($this, "Cannot update a pass that has been payed for or cancelled");
		$pass->name = $name;
		$pass->save();
		return $ticket->for_json_with_coupons();
	}
	
	protected function delete($id) {
		$pass = new Model_User_Pass((int)$id);
		if (!$pass->loaded())
			throw new Api_Exception_InvalidInput($this, "No pass found for '$id'");
		if (!($this->systemAccessAllowed() or $pass->user_id == $this->user->pk()))
			throw new Api_Exception_Unauthorized($this, "Not authorized to delete passes");
		$data = $this->input();
		if ($data->delete) {
			if ($pass->isAuthorized())
				throw new Api_Exception_InvalidInput($this, "Can't delete authorized passes, cancel it first");
			$pass->returnCoupons();
			$pass->delete();
		} else { // caller doesn't really want to delete, try to cancel or refund
			$reason = $data->reason ?: "User " . $this->user->email . " cancelled";
			if ($pass->isAuthorized()) {
				if (!$this->systemAccessAllowed()) // only system can refund authorized passes
					throw new Api_Exception_Unauthorized($this, "Not authorized to delete passes");
				$refundType = new Model_Coupon_Type($data->refund_coupon_type);
				if (!$refundType->loaded())
					throw new Api_Exception_InvalidInput($this, "Can't refund user pass without a refund coupon");
				Logger::debug("Starting refunding {$pass} by {$this->user} using {$refundType}");
				if ($pass->price > 0 and !$refundType->loaded())
					throw new Api_Exception_InvalidInput($this, "User pass already authorized, and no \"refund-coupon-type\" specified : ".print_r($data->delete,true));
				$pass->refund($refundType, $reason);
			} else {
				$pass->cancel($reason);
			}
		}
		return true;
	}
	
	protected function catalog() {
		$data = $this->input();
		// two different base modes - user and admin/convention
		$filters = [];
		
		if ($data->for_timeslot) {
			$ts = new Model_Timeslot($data->for_timeslot);
			if (!$ts->loaded())
				throw new Api_Exception_InvalidInput($this, "No timeslot '" . $data->for_timeslot . "'");
			list ($available, $unavailable) = Model_User_Pass::timeslot_report($this->getValidUser(), $ts);
			$available = ORM::result_for_json($available);
			$unavailable = ORM::result_for_json($unavailable);
			$passes = array_merge(
					array_map(function($pass){
						$pass['available'] = true;
						return $pass;
					}, $available),
					array_map(function($pass){
						$pass['available'] = false;
						return $pass;
					}, $unavailable)
					);
			usort($passes, function($a, $b){
				return $a['id'] - $b['id'];
			});
			return $passes;
		}
		
		if ($data->all and $this->systemAccessAllowed()) {
			// ehmm.. no default filters, unless the caller asked for a user filter
			if ($data->by_user) {
				if (is_numeric($data->by_user))
					$filters['user_pass.user_id'] = $data->by_user;
				else
					$filters['email'] = $data->by_user;
			}
		} else {
			// verify user and filter by them
			$filters['user_pass.user_id'] = $this->getValidUser()->pk();
		}
		
		if ($data->is_valid)
			$filters['valid'] = 1;
		
		return ORM::result_for_json($this->convention->getPasses($filters), 'for_json_with_coupons');
	}
	
	private function getValidUser() {
		if ($this->systemAccessAllowed() and $this->input()->user)
			return $this->loadUserByIdOrEmail($this->input()->user);
		if ($this->user) // user authenticated themselves - fine
			return $this->user;
		throw new Api_Exception_InvalidInput($this, "User must be authenticated or specified by an authorized convention");
	}
	
}