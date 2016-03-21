<?php

abstract class Api_Rest_Controller extends Api_Controller {
	
	protected $convention = null;
	protected $user = null;
	
	public function action_index() {
		$this->convention = $this->verifyConventionKey();
		try {
			$this->user = $this->verifyAuthentication()->user;
		} catch (Api_Exception_Unauthorized $e) {
			// some APIs allow no user auth
		}
		
		switch ($this->request->method()) {
			case 'POST':
				$obj = $this->create();
				if (is_null($obj))
					$this->send([ 'status' => false ]);
				else
					$this->send([ 'status' => true, 'id' => $obj->pk() ]);
				return;
			case 'GET':
				$this->send(
					$this->retrieve($this->request->param('id'))
				);
				return;
			case 'PUT':
				$this->send([
					'status' => $this->update($this->request->param('id'))
				]);
				return;
			case 'DELETE':
				$this->send([
					'status' => $this->delete($this->request->param('id'))
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
	abstract protected function create();
	
	/**
	 * Retrieve an existing record by ID
	 * @param Model_Convention $con Convention that owns the record
	 * @param Model_User $user User that is trying to access
	 * @param int $id record ID
	 * @return stdClass Record data
	 */	
	abstract protected function retrieve($id);
	
	/**
	 * Update an existing record
	 * @param Model_Convention $con Convention that owns the record
	 * @param Model_User $user User that is trying to access
	 * @param int $id record ID
	 * @param stdClass $data Data to update the record
	 * @return boolean Whether the create succeeded
	 */
	abstract protected function update($id);
	
	/**
	 * Delete an existing record
	 * @param Model_Convention $con Convention that owns the record
	 * @param Model_User $user User that is trying to access
	 * @param unknown $id record ID
	 * @return boolean Whther the delete succeeded
	 */
	abstract protected function delete($id);
}
