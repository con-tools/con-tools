<?php
class Controller_Entities_Records extends Api_Controller {
	
	public function action_index() {
		$user = $this->verifyAuthentication()->user;
		$con = $this->verifyConventionKey();
		switch ($this->request->method()) {
			case 'POST':
				return $this->create($con, $user, json_decode($this->request->body(), true));
			case 'PUT':
				return $this->update($con, $user, $this->request->param('id'), json_decode($this->request->body(), true));
			case 'DELETE':
				return $this->delete($con, $user, $this->request->param('id'));
			case 'GET':
				return $this->retrieve($con, $user, $this->request->param('id'));
		}
	}
	
	private function update(Model_Convention $con, Model_User $user, $id, $data) {
		$record = Model_User_Record::byDescriptor($con, $user, $id);
		$record->data = $data['data'];
		$record->content_type = $data['content_type'];
		$record->save();
		$this->send(['status' => true]);
	}
	
	private function delete(Model_Convention $con, Model_User $user, $id) {
		Model_User_Record::byDescriptor($con, $user, $id)->delete();
		$this->send(['status' => true]);
	}
	
	private function create(Model_Convention $con, Model_User $user, $data) {
		Model_User_Record::persist($con, $user, $data['descriptor'], $data['content_type'], $data['data']);
		$this->send(['status' => true]);
	}
	
	private function retrieve(Model_Convention $con, Model_User $user, $id) {
		$this->send(Model_User_Record::byDescriptor($con, $user, $id)->as_array());
	}
}
