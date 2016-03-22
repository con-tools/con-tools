<?php

class Controller_Entities_Managers extends Api_Rest_Controller {
	
	/**
	 * Create a new record
	 * @param stdClass $data Data to create the record
	 * @return ORM Model object created
	 */
	protected function create() {
		$data = $this->input();
		if (!$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to add managers!");
		$user_id = $data->id;
		$email = $data->email;
		if ($user_id and $email)
			throw new Api_Exception_InvalidInput($this, "Please provide either an `id` or `email` but not both");
		if ($user_id) {
			$user = new Model_User($user_id);
			if (!$user->loaded())
				throw new Api_Exception_InvalidInput($this, "Invalid user specified");
		} elseif ($email) {
			try {
				$user = Model_User::byEmail($email);
			} catch (Model_Exception_NotFound $e) {
				throw new Api_Exception_InvalidInput($this, "Invalid user specified");
			}
		} else {
			throw new Api_Exception_InvalidInput($this, "Invalid user specified");
		}
		$this->convention->addManager($user);
		return array_merge($user->for_public_json(), [ 'id' => $user->pk() ]);
	}
	
	/**
	 * Retrieve an existing record by ID
	 * @param int $id record ID
	 * @return stdClass Record data
	 */
	protected function retrieve($id) {
		throw new Api_Exception_Unimplemented($this);
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
		if (!$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to add managers!");
		if (!$id)
			throw new Api_Exception_InvalidInput($this, "Invalid user specified");
		$user = new Model_User($id);
		if (!$user->loaded())
			return true;
		if ($this->user == $user)
			throw new Api_Exception_InvalidInput($this, "You cannot remove yourself!");
		$this->convention->removeManager($user);
		return true;
	}
	
	/**
	 * Retrieve the list of managers for the convention
	 * @return array
	 */
	protected function catalog() {
		if (!$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to list managers!");
		$managers = [];
		foreach ($this->convention->managers->find_all() as $management) {
			$user = $management->user;
			$managers[] = [
					'id' => $user->pk(),
					'email' => $user->email,
					'name' => $user->name,
			];
		}
		return $managers;
	}
	
}
