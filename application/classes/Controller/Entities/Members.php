<?php

class Controller_Entities_Members extends Api_Rest_Controller {
	
	public function create() {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to create members");
		$data = $this->input();
		if (!$data->user)
			throw new Api_Exception_InvalidInput($this, "Required field 'user' is missing");
		$user = $this->loadUserByIdOrEmail(is_array($data->user) ? @$data->user['id'] : $data->user,
				is_array($data->user) ? @$data->user['email'] : $data->user);
		if (!$data->membership)
			throw new Api_Exception_InvalidInput($this, "Required field 'membership' is missing");
		if (!$data->organizer)
			throw new Api_Exception_InvalidInput($this, "Required field 'organizer' is missing");
		$organizer = new Model_Organizer($data->organizer);
		if (!$organizer)
			try {
				$organizer = Model_Organizer::byTitle($data->organizer);
			} catch (Model_Exception_NotFound $e) {
				throw new Api_Exception_InvalidInput($this, "Required field 'organizer' is invalid");
			}
		return Model_Organization_Member::persist($organizer, $user, $data->membership);
	}
	
	public function retrieve($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to get membership data");
		$member = new Model_Organization_Member($id);
		if ($member->loaded())
			return $member->for_json();
		throw new Api_Exception_InvalidInput($this, "No membership record $id found");
	}
	
	public function update($id) {
		throw new Api_Exception_Unimplemented($this);
	}
	
	public function delete($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to delete members");
		$member = new Model_Organization_Member($id);
		if ($member->loaded())
			$member->delete();
		return true;
	}
	
	public function catalog() {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not allowed to list members");
		
		return ORM::result_for_json(Model_Organization_Member::getByConvention($this->convention));
	}
}
