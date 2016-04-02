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
		return Model_Coupon::persist($couponType, $user)->for_json();
	}
	
	public function retrieve($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to list coupons");
		$coupon = new Model_Coupon($id);
		if ($coupon->loaded())
			return $coupon->for_json();
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
		if ($coupon->loaded())
			$coupon->delete();
		return true;
	}
	
	public function catalog() {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to list coupons");
		return ORM::result_for_json(Model_Coupon::byConvention($this->convention), 'for_json_With_tickets');
	}
	
}