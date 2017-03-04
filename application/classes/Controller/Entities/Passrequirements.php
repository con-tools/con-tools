<?php

class Controller_Entities_Passrequirements extends Api_Rest_Controller {
	
	public function create() {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this);
		$data = $this->input();
		if (!$data->title)
			throw new Api_Exception_InvalidInput($this, "Missing title!");
		$passreq = Model_Pass_Requirement::persist($this->convention, $data->title);
		if ($data->start_time and $data->end_time) {
			$passreq->start_time = new DateInterval($data->start_time);
			$passreq->end_time = new DateInterval($data->end_time);
			$passreq->save();
		}
		return $passreq;
	}
	
	public function retrieve($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this);
		$passreq = new Model_Pass_Requirement($id);
		if (!$passreq->loaded())
			throw new Api_Exception_Notfound($this);
		return $passreq->for_json();
	}
	
	public function update($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this);
		throw new Api_Exception_Unimplemented($this);
	}
	
	public function delete($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this);
			$passreq = new Model_Pass_Requirement($id);
			if (!$passreq->loaded())
				throw new Api_Exception_Notfound($this);
			$passreq->delete();
			return true;
	}
	
	public function catalog() {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this);
		return ORM::result_for_json($this->convention->pass_requirements->find_all());
	}
	
}
