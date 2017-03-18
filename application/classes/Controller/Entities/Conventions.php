<?php
class Controller_Entities_Conventions extends Api_Rest_Controller {

	/**
	 * We should be able to create a convention without its key (that doesn't exist yet)
	 */
	protected function tryAuthenticate() {
		try {
			parent::tryAuthenticate();
		} catch (Api_Exception_Unauthorized $e) {}
	}
	
	protected function update($id) {
		if ($id == 'self') { // self lookup for a convention
			$con = $this->verifyConventionKey();
			error_log('Looking up self convention id: ' . $con->pk());
		} else {
			$con = new Model_Convention($id);
		}

		if (is_null($con) || !$con->loaded() || !$con->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to update convention!");
		$data = $this->input();
		if ($data->settings)
			$con->settings = $data->settings;
		$con->save();
		return true;
	}
	
	protected function delete($id) {
		throw new Api_Exception_Unimplemented($this);
	}
	
	protected function create() {
		if (is_null($this->user))
			throw new Api_Exception_Unauthorized($this, "Must be logged in!");
		$user = $this->user;
		$data = $this->input()->getFields();
		if (!isset($data['title']))
			throw new Api_Exception_InvalidInput($this, "Missing title");
		try {
			$con = Model_Convention::persist($data['title'], @$data['series'], @$data['website'], @$data['location'], @$data['slug']);
			$key = $con->generateApiKey();
			$owner = Model_Manager::persist($con, $user, (new Model_Role_Manager())->getRole());
			return [
					'slug' => $con->slug,
					'id' => $con->id,
					'key' => $key->client_key,
					'secret' => $key->client_secret,
			];
		} catch (Api_Exception_Duplicate $e) {
			throw new Api_Exception_InvalidInput($this, "Convention {$data['title']} already exists");
		}
	}

	protected function retrieve($id) {
		if ($id == 'self') { // self lookup for a convention
			$con = $this->verifyConventionKey();
			error_log('Looking up self convention id: ' . $con->pk());
			try {
				if ($this->systemAccessAllowed())
					return $con->for_private_json();
			} catch (Api_Exception_Unauthorized $e) { }
			return $con->for_json();
		}
		
		$con = new Model_Convention($id);
		if (!$con->loaded())
			throw new Model_Exception_NotFound();
		return [
				'id' => $con->id,
				'title' => $con->title,
				'slug' => $con->slug,
				'series' => $con->series,
		];
	}
	
	protected function catalog() {
		return array_map(function($con) {
			$condata = [
					'id' => $con->id,
					'title' => $con->title,
					'slug' => $con->slug,
					'series' => $con->series,
			];
			if ($this->request->query('keys')) {
				foreach ($con->api_keys->find_all() as $key) {
					$condata['public-key'] = $key->client_key;
					break;
				}
			}
			return $condata;
		}, ORM::factory('Convention')->find_all()->as_array());
	}
	
}
