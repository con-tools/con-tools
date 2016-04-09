<?php

class Controller_Entities_MerchandiseSKUs extends Api_Rest_Controller {
	
	public function create() {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to create merchandise SKUs");
		$data = $this->input();
		if (!($title = $data->title))
			throw new Api_Exception_InvalidInput($this, "Mandatory parameter title missing");
		if (!($code = $data->code))
			throw new Api_Exception_InvalidInput($this, "Mandatory parameter code missing");
		if (!($price = $data->price))
			throw new Api_Exception_InvalidInput($this, "Mandatory parameter price missing");
		$description = $data->description;
		return Model_Merchandise_Sku::persist($this->convention, $title, $code, $price, $description)->for_json();
	}
	
	public function retrieve($id) {
		$sku = new Model_Merchandise_Sku($id);
		if ($sku->loaded())
			return $sku->for_json();
		throw new Api_Exception_Notfound($this);
	}
	
	public function update($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to delete merchandise SKUs");
		$sku = new Model_Merchandise_Sku($id);
		if (!$sku->loaded())
			throw new Api_Exception_Notfound($this);
		$data = $this->input();
		$title = $data->title;
		$price = $data->price;
		$description = $data->description;
		if ($title)
			$sku->title = $title;
		if ($description)
			$sku->description = $description;
		if ($price)
			$sku->price = $price;
		return $sku->save()->for_json();
	}
	
	public function delete($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to delete merchandise SKUs");
		$sku = new Model_Merchandise_Sku($id);
		if ($sku->loaded())
			$sku->delete();
		return true;
	}
	
	public function catalog() {
		return ORM::result_for_json(Model_Merchandise_Sku::queryForConvention($this->convention)->find_all());
	}
}