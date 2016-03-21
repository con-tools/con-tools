<?php

abstract class Api_Rest_Controller extends Api_Controller {
	
	public function action_index() {
		$con = $this->verifyConventionKey();
		$user = $this->verifyAuthentication()->user;
		switch ($this->request->method()) {
			case 'POST':
				$obj = $this->create($con, $user);
				if (is_null($obj))
					$this->send([ 'status' => false ]);
				else
					$this->send([ 'status' => true, 'id' => $obj->pk() ]);
				return;
			case 'GET':
				$this->send(
					$this->retrieve($con, $user, $this->request->param('id'))
				);
				return;
			case 'PUT':
				$this->send([
					'status' => $this->update($con, $user, $this->request->param('id'))
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
	 * @param stdClass $data Data to create the record
	 * @return ORM Model object created
	 */
	abstract protected function create(Model_Convention $con, Model_User $user);
	
	/**
	 * Retrieve an existing record by ID
	 * @param Model_Convention $con Convention that owns the record
	 * @param Model_User $user User that is trying to access
	 * @param int $id record ID
	 * @return stdClass Record data
	 */	
	abstract protected function retrieve(Model_Convention $con, Model_User $user, $id);
	
	/**
	 * Update an existing record
	 * @param Model_Convention $con Convention that owns the record
	 * @param Model_User $user User that is trying to access
	 * @param int $id record ID
	 * @param stdClass $data Data to update the record
	 * @return boolean Whether the create succeeded
	 */
	abstract protected function update(Model_Convention $con, Model_User $user, $id);
	
	/**
	 * Delete an existing record
	 * @param Model_Convention $con Convention that owns the record
	 * @param Model_User $user User that is trying to access
	 * @param unknown $id record ID
	 * @return boolean Whther the delete succeeded
	 */
	abstract protected function delete(Model_Convention $con, Model_User $user, $id);
}
