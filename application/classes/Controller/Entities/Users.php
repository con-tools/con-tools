<?php

class Controller_Entities_Users extends Api_Rest_Controller {
	
	/**
	 * Create a new record
	 * @return ORM Model object created
	 */
	protected function create() {
		if ($this->systemAccessAllowed()) {
			$data = $this->input();
			if ($data->email && $data->name) {
				$user = Model_User::persist($data->name, $data->email, "manager-added", 'manager-added-' . bin2hex(random_bytes(4)) );
				if ($data->phone) {
					$user->phone = $data->phone;
					$user->save();
				}
				return ($user)->for_json();
			}
			throw new Api_Exception_InvalidInput($this, "Must provide 'email' and 'name'");
		}
		throw new Api_Exception_Unauthorized($this, "Cannot add users. Please try to login as the user first.");
	}
	
	/**
	 * Retrieve an existing record by ID
	 * @param int $id record ID
	 * @return stdClass Record data
	 */
	protected function retrieve($id) {
		if (!$this->systemAccessAllowed() and !($this->user and $this->user->pk() == $id))
			throw new Api_Exception_Unauthorized($this, "Not authorized to lookup users!");
		$user = $this->loadUserByIdOrEmail($id);
		return array_merge($user->for_json(), [
						'coupons' => ORM::result_for_json(Model_Coupon::byConventionUser($this->convention, $user)),
						'tickets' => ORM::result_for_json(Model_Ticket::byConventionUser($this->convention, $user)),
				]);
	}
	
	/**
	 * Update an existing record
	 * @param int $id record ID
	 * @param stdClass $data Data to update the record
	 * @return boolean Whether the create succeeded
	 */
	protected function update($id) {
		throw new Api_Exception_Unimplemented($this);
	}
	
	/**
	 * Delete an existing record
	 * @param int $id record ID
	 * @return boolean Whether the delete succeeded
	 */
	protected function delete($id) {
		throw new Api_Exception_Unimplemented($this);
	}
	
	/**
	 * Retrieve the list of users of the convention
	 * @return array
	 */
	protected function catalog() {
		if (!$this->convention or !$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to list users!");
		$users = [];
		if ($this->input()->email) {
			try {
				return (Model_User::byEmail($this->input()->email))->for_json();
			} catch (Model_Exception_NotFound $e) {
				return null;
			}
		}
		if ($this->input()->convention)
			return ORM::result_for_json(Model_User::byConvention($this->convention));
		return ORM::result_for_json(Model_User::all());
	}
	
}
