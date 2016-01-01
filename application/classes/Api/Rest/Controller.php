<?php

abstract class Api_Rest_Controller extends Api_Controller {
	
	public function action_index() {
		$con = $this->verifyConventionKey();
		$user = $this->verifyAuthentication()->user;
		switch ($this->request->method()) {
			case 'POST':
				$this->send([
					'status' => $this->create($con, $user, json_decode($this->request->body(), true))
				]);
			case 'GET':
				$this->send(
					$this->retrieve($con, $user, $this->request->param('id'))
				);
				return;
			case 'PUT':
				$this->send([
					'status' => $this->update($con, $user, $this->request->param('id'), json_decode($this->request->body(), true))
				]);
				return;
			case 'DELETE':
				$this->send([
					'status' => $this->delete($con, $user, $this->request->param('id'))
				]);
				return;
			default:
				throw new Exception("Invalid operation {$this->request->method()}");
		}
	}
	
	/**
	 * Create a new record
	 * @param Model_Convention $con Convention that owns the record
	 * @param Model_User $user User that is trying to access
	 * @param array $data Data to create the record
	 * @return boolean Whether the create succeeded
	 */
	abstract function create(Model_Convention $con, Model_User $user, $data);
	
	/**
	 * Retrieve an existing record by ID
	 * @param Model_Convention $con Convention that owns the record
	 * @param Model_User $user User that is trying to access
	 * @param int $id record ID
	 * @return stdClass Record data
	 */	
	abstract function retrieve(Model_Convention $con, Model_User $user, $id);
	
	/**
	 * Update an existing record
	 * @param Model_Convention $con Convention that owns the record
	 * @param Model_User $user User that is trying to access
	 * @param int $id record ID
	 * @param unknown $data Data to update the record
	 * @return boolean Whether the create succeeded
	 */
	abstract function update(Model_Convention $con, Model_User $user, $id, $data);
	
	/**
	 * Delete an existing record
	 * @param Model_Convention $con Convention that owns the record
	 * @param Model_User $user User that is trying to access
	 * @param unknown $id record ID
	 * @return boolean Whther the delete succeeded
	 */
	abstract function delete(Model_Convention $con, Model_User $user, $id);
}
