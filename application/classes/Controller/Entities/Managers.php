<?php

class Controller_Entities_Managers extends Api_Rest_Controller {
	
	/**
	 * Create a new record
	 * @param stdClass $data Data to create the record
	 * @return ORM Model object created
	 */
	protected function create() {}
	
	/**
	 * Retrieve an existing record by ID
	 * @param int $id record ID
	 * @return stdClass Record data
	 */
	protected function retrieve($id) {}
	
	/**
	 * Update an existing record
	 * @param int $id record ID
	 * @param stdClass $data Data to update the record
	 * @return boolean Whether the create succeeded
	 */
	protected function update($id) {}
	
	/**
	 * Delete an existing record
	 * @param int $id record ID
	 * @return boolean Whether the delete succeeded
	 */
	protected function delete($id) {}
	
	/**
	 * Retrieve the list of managers for the convention
	 * @return array
	 */
	protected function catalog() {
		if (is_null($this->user) or !$this->convention->isManager($this->user))
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
