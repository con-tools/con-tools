<?php
class Controller_Entities_Conventions extends Api_Controller {
	
	public function action_index() {
		switch ($this->request->method()) {
			case 'POST':
				$user = $this->verifyAuthentication()->user;
				return $this->create($user, json_decode($this->request->body(), true));
			case 'PUT':
				$user = $this->verifyAuthentication()->user;
				return $this->update($user, $this->request->param('id'), json_decode($this->request->body(), true));
			case 'DELETE':
				$user = $this->verifyAuthentication()->user;
				return $this->delete($user, $this->request->param('id'));
			case 'GET':
				return $this->retrieve($this->request->param('id'));
		}
	}
	
	private function update(Model_User $user, $id, $data) {
		throw new Api_Exception_Unimplemented($this);
	}
	
	private function delete(Model_User $user, $id) {
		throw new Api_Exception_Unimplemented($this);
	}
	
	private function create(Model_User $user, $data) {
		if (!isset($data['title']))
			return $this->send(['status' => false, 'error' => 'missing title']);
		try {
			$con = Model_Convention::persist($data['title'], @$data['series'], @$data['website'], @$data['location'], @$data['slug']);
			$key = $con->generateApiKey();
			$owner = Model_Manager::persist($con, $user, (new Model_Role_Manager())->getRole());
			$this->send([
					'status' => true,
					'slug' => $con->slug,
					'id' => $con->id,
					'key' => $key->client_key,
					'secret' => $key->client_secret,
			]);
		} catch (Api_Exception_Duplicate $e) {
			$this->send([
					'status' => false,
					'error' => "Convention {$data['title']} already exists"
			]);
		}
	}

	private function retrieve($id) {
		if ($id) {
			try {
				$con = new Model_Convention($id);
				if (!$con->loaded())
					throw new Model_Exception_NotFound();
				$this->send([
						'id' => $con->id,
						'title' => $con->title,
						'slug' => $con->slug,
						'series' => $con->series,
				]);
			} catch (Model_Exception_NotFound $e) {
				$this->send(['data'=> null]);
			}
		} else {
			$out = [];
			foreach (ORM::factory('Convention')->find_all() as $con) {
				$out[] = [
						'id' => $con->id,
						'title' => $con->title,
						'slug' => $con->slug,
						'series' => $con->series,
				];
			}
			$this->send($out);
		}
	}
	
}
