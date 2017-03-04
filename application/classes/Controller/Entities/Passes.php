<?php

class Controller_Entities_Passes extends Api_Rest_Controller {
	
	public function create() {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this);
		$data = $this->input();
		if (!$data->title)
			throw new Api_Exception_InvalidInput($this, "Title must be specified");
		if (!$data->price or !is_numeric($data->price))
			throw new Api_Exception_InvalidInput($this, "Price must be specified");
		$pass = Model_Pass::persist($this->convention, $data->title, $data->public, $data->price);
		return $pass->for_json();
	}
	
	public function retrieve($id) {
		$pass = new Model_Pass($id);
		if ($pass->loaded() && $pass->public)
			return $pass->for_json();
		throw new Api_Exception_InvalidInput($this, "No valid passes found to display");
	}
	
	public function update($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this);
		$pass = new Model_Pass((int)$id);
		if (!$pass->loaded())
			throw new Api_Exception_InvalidInput($this, "No pass found");
		$data = $this->input();
		// allow price to be updated
		if ($data->price and is_numeric($data->price))
			$pass->price = $data->price;
		// allow public flag to be changed
		if ($data->isset('public'))
			$pass->public = $data->public ? true : false;
		// allow requirements to be assigned
		if ($data->isset('pass_requirements')) {
			$ids = !is_array($data->pass_requirements) ? [ $data->pass_requirements ] : $data->pass_requirements;
			$passreqs = array_map(function($id) {
				$passreq = new Model_Pass_Requirement($id);
				if (!$passreq->loaded())
					throw new Api_Exception_InvalidInput($this, "Invalid pass requirement ID $id");
				return $passreq;
			}, $ids);
			foreach ($passreqs as $passreq)
				$pass->add('pass_requirements', $passreq);
		}
		$pass->save();
		return $pass->for_json();
	}
	
	public function delete($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not authorized to delete passes");
		$pass = new Model_Pass((int)$id);
		if (!$pass->loaded())
			throw new Api_Exception_InvalidInput($this, "No pass found for '$id'");
		$pass->cancel();
		return true;
	}
	
	public function catalog() {
		$data = $this->input();
		// two different base modes - user and admin/convention
		if ($data->all and $this->systemAccessAllowed()) {
			return ORM::result_for_json($this->convention->passes->
					where('status','=',Model_Pass::STATUS_ACTIVE)->
					find_all());
		} else {
			return ORM::result_for_json($this->convention->passes->
					where('status','=',Model_Pass::STATUS_ACTIVE)->
					where('public','=',true)->
					find_all());
		}
	}
	
}
