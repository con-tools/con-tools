<?php
class Controller_Entities_Records extends Api_Controller {
	
	public function action_index() {
		$con = $this->verifyConventionKey();
		$user = null;
		try {
			$user = $this->verifyAuthentication()->user;
		} catch (Api_Exception_Unauthorized $e) {
			if (!$con->isAuthorized() && $this->tryHandlePublicRetrieve($con))
				return;
			throw $e;
		}
		if (is_null($user) && $con->isAuthorized()) {// check if convention want to work on a per-user record
			if ($access_user = $this->request->query('user')) {
				try {
					$user = Model_User::byEmail($access_user);
				} catch (Model_Exception_NotFound $e) {
					throw new HTTP_Exception_404("User not found");
				}
			}
		}
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
		if ($data['acl'] && Model_User_Record::isValidACL($data['acl']))
			$record->acl = $data['acl'];
		$record->save();
		$this->send(['status' => true]);
	}
	
	private function delete(Model_Convention $con, Model_User $user, $id) {
		Model_User_Record::byDescriptor($con, $user, $id)->delete();
		$this->send(['status' => true]);
	}
	
	private function create(Model_Convention $con, Model_User $user, $data) {
		Model_User_Record::persist($con, $user, $data['descriptor'], $data['content_type'], $data['data'], $data['acl']);
		$this->send(['status' => true]);
	}

	private function retrieve(Model_Convention $con, Model_User $user, $id) {
		if (!$user && $con->isAuthorized()) {// convention wants a catalog
			$records = Model_User_Record::allByDescriptor($con, $id);
			foreach ($records as &$record)
				$records = $records->as_array();
			return $this->send(['data' => $records]);
		}
			
		try {
			$this->send(['data' => Model_User_Record::byDescriptor($con, $user, $id)->as_array()]);
		} catch (Model_Exception_NotFound $e) {
			$this->send(['data'=> null]);
		}
	}
	
	private function tryHandlePublicRetrieve(Model_Convention $con) {
		$public_access_user = $this->request->query('user');
		if (!$public_access_user) {
			error_log("Trying public access - no user");
			return false;
		}
		try {
			$public_access_user = Model_User::byEmail($public_access_user);
		} catch (Model_Exception_NotFound $e) {
			error_log("Trying public access - user $public_access_user not found");
			return false;
		}
		
		$id = $this->request->param('id');
		try {
			$record = Model_User_Record::byDescriptor($con, $public_access_user, $id);
			if ($record->isPublicReadable()) {
				$this->send(['data' => $record->as_array()]);
				return true;
			}
			error_log("Trying public access, record not public: " . $record->acl);
		} catch (Model_Exception_NotFound $e) {
			error_log("Trying public access, no record $id found");
		}
		return false;
	}
}
