<?php

class Controller_Entities_Coupontypes extends Api_Rest_Controller {

	public function create() {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to list coupons");
		$data = $this->input();
		if (!$data->title)
			throw new Api_Exception_InvalidInput($this, "Mandatory field 'title' is missing");
		if (!$data->type)
			throw new Api_Exception_InvalidInput($this, "Madatory field 'type' is missing");
		if (!$data->value)
			throw new Api_Exception_InvalidInput($this, "Mandatory field 'value' is missing");
		if (!$data->category)
			throw new Api_Exception_InvalidInput($this, "Mandatory field 'category' is missing");
		if (!is_bool($data->multiuse))
			throw new Api_Exception_InvalidInput($this, "Mandatory boolean field 'multiuse' is missing or invalid");
		return Model_Coupon_Type::persist($this->convention, $data->title, stristr($data->type, 'fixed')?true:false,
				$data->value, $data->category, $data->multiuse, $data->code)->for_json();
	}

	public function retrieve($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to list coupons");
		$coupontype = new Model_Coupon_Type($id);
		if ($coupontype->loaded())
			return $coupontype->for_json();
		throw new Api_Exception_InvalidInput($this, "Failed to find coupon type $id");
	}

	public function update($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to list coupons");
		throw new Api_Exception_Unimplemented($this);
	}

	public function delete($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to list coupons");
		$coupontype = new Model_Coupon_Type($id);
		if ($coupontype->loaded())
			$coupontype->delete();
		return true;
	}

	public function catalog() {
		return ORM::result_for_json(Model_Coupon_Type::byConvention($this->convention));
	}

}