<?php
class Controller_Entities_Conventions extends Api_Controller {
	
	public function action_index() {
		$user = $this->verifyAuthentication()->user;
		switch ($this->request->method()) {
			case 'POST':
				return $this->create($user, json_decode($this->request->body(), true));
			case 'PUT':
				return $this->update($user, $this->request->param('id'), json_decode($this->request->body(), true));
			case 'DELETE':
				return $this->delete($user, $this->request->param('id'));
			case 'GET':
				return $this->retrieve($user, $this->request->param('id'));
		}
	}
	
	private function update(Model_Convention $con, Model_User $user, $id, $data) {
		throw new Api_Exception_Unimplemented($this);
	}
	
	private function delete(Model_Convention $con, Model_User $user, $id) {
		throw new Api_Exception_Unimplemented($this);
	}
	
	private function create(Model_Convention $con, Model_User $user, $data) {
		$con = Model_Convention::persist($data['title'], $data['series'], $data['location'], @$location['slug']);
		$key = $con->generateApiKey();
		$this->send([
				'status' => true,
				'slug' => $con->slug,
				'id' => $con->id,
				'key' => $key->client_key,
				'secret' => $key->client_secret,
		]);
	}

	private function retrieve(Model_Convention $con, Model_User $user = null, $id) {
		try {
			$con = new Model_Convention($id);
			if (!$con->loaded())
				throw new Model_Exception_NotFound();
			$this->send(['data' => $con]);
		} catch (Model_Exception_NotFound $e) {
			$this->send(['data'=> null]);
		}
	}
	
}
