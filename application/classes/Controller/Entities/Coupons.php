<?php

class Controller_Entities_Coupons extends Api_Rest_Controller {
	
	public function create() {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to list coupons");
		$data = $this->input();
		if (!$data->type)
			throw new Api_Exception_InvalidInput($this, "Mandatory field 'type' is missing");
		$couponType = new Model_Coupon_Type($data->type);
		if (!$couponType->loaded())
			throw new Api_Exception_InvalidInput($this, "Mandatory field 'type' is invalid");
		if (!$data->user)
			throw new Api_Exception_InvalidInput($this, "Mandatory field 'user' is missing");
		$user = $this->loadUserByIdOrEmail(is_array($data->user) ? @$data->user['id'] : null,
				is_array($data->user) ? @$data->user['email'] : $data->user);
		return Model_Coupon::persist($couponType, $user, "Created by " . ($this->user ? $this->user->email : "convention"))->for_json_With_tickets();
	}
	
	public function retrieve($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to list coupons");
		$coupon = new Model_Coupon($id);
		if ($coupon->loaded())
			return $coupon->for_json_With_tickets();
		throw new Api_Exception_InvalidInput($this, "Failed to find coupong type $id");
	}
	
	public function update($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to list coupons");
		throw new Api_Exception_Unimplemented($this);
	}
	
	public function delete($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to list coupons");
		$coupon = new Model_Coupon($id);
		if ($coupon->loaded()) {
			if ($coupon->ticket_id)
				throw new Api_Exception_InvalidInput($this, "Cannot remove used coupon");
			$coupon->delete();
		}
		return true;
	}
	
	public function catalog() {
		$data = $this->input();
		if (!$this->systemAccessAllowed() || $data->self) {
			if ($this->user)
				return $this->getUserCoupons();
			return [];
		}
		$filters = [];
		if ($data->by_type)
			$filters['coupon_type_id'] = $data->by_type;
		if ($data->by_user)
			$filters['user_id'] = $this->loadUserByIdOrEmail($data->by_user)->pk();
		return ORM::result_for_json(
				array_filter(Model_Coupon::byConvention($this->convention)->as_array(),
						function($coupon) use ($filters) {
							foreach ($filters as $field => $value) {
								if ($coupon->get($field) != $value)
									return false;
							}
							return true;
						}), 'for_json_With_tickets');
	}
	
	private function getUserCoupons() {
		$data = $this->input();
		return ORM::result_for_json( Model_Coupon::unconsumedForUser($this->user, $this->convention));
	}
	
}