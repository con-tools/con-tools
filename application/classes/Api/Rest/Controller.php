<?php

class Api_Rest_Controller extends Api_Controller {
	
	public function action_index() {
		$con = $this->verifyConventionKey();
		$user = $this->verifyAuthentication()->user;
		switch ($this->request->method()) {
			case 'POST':
				return $this->create($con, $user, json_decode($this->request->body(), true));
			case 'GET':
				return $this->retrieve($con, $user, $this->request->param('id'));
			case 'PUT':
				return $this->update($con, $user, $this->request->param('id'), json_decode($this->request->body(), true));
			case 'DELETE':
				return $this->delete($con, $user, $this->request->param('id'));
			default:
				throw new Exception("Invalid operation {$this->request->method()}");
		}
	}
	
	abstract function create(Model_Convention $con, Model_User $user, $data);
	abstract function retrieve(Model_Convention $con, Model_User $user, $id);
	abstract function update(Model_Convention $con, Model_User $user, $id, $data);
	abstract function delete(Model_Convention $con, Model_User $user, $id);
}