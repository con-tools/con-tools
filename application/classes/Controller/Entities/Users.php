<?php

class Controller_Entities_Users extends Api_Rest_Controller {
	
	/**
	 * Create a new record
	 * @return ORM Model object created
	 */
	protected function create() {
		throw new Api_Exception_Unimplemented($this, "Cannot add users. Please try to login as the user first.");
	}
	
	/**
	 * Retrieve an existing record by ID
	 * @param int $id record ID
	 * @return stdClass Record data
	 */
	protected function retrieve($id) {
		if (!$this->convention or !$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to lookup users!");
		$user = $this->loadUserByIdOrEmail($id, $id);
		return $user->for_json();
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
		foreach ((new Model_User)->find_all() as $user) {
			$users[] = [
					'id' => $user->pk(),
					'email' => $user->email,
					'name' => $user->name,
			];
		}
		return $users;
	}
	
}
