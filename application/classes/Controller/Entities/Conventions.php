<?php
class Controller_Entities_Conventions extends Api_Rest_Controller {

	/**
	 * We should be able to create a convention without its key (that doesn't exist yet)
	 */
	protected function tryAuthenticate() {
		try {
			parent::tryAuthenticate();
		} catch (Api_Exception_Unauthorized $e) {
			// even if we failed to authenticate (likely because missing convention header - this controller accepts calls like that)
			// try to auth the user
			try {
				$this->user = $this->verifyAuthentication()->user;
			} catch (Exception $e) {}
		}
	}
	
	protected function update($id) {
		if ($id == 'self') { // self lookup for a convention
			$con = $this->verifyConventionKey();
			Logger::debug('Looking up self convention id: ' . $con->pk());
		} else {
			$con = new Model_Convention($id);
		}

		if (is_null($con) || !$con->loaded() || !$con->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to update convention!");
		$data = $this->input();
		foreach ([ 'start_date', 'end_date' ] as $field) {
			if ($data->$field)
				$con->$field = $this->parseDateTime($data->$field);
		}
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
			Logger::debug('Looking up self convention id: ' . $con->pk());
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
		$data = $this->input();
		if($data->manager) {
			if (!$this->user)
				throw new Api_Exception_InvalidInput($this, "Can't list managed conventions without a log in");
			$query = (new Model_Convention())->join('managers', 'INNER')
				->on('managers.convention_id','=','convention.id')
				->where('managers.user_id', '=', $this->user->pk());
		} else
			$query = (new Model_Convention());
		
		return array_map(function($con) use ($data) {
			$condata = [
					'id' => $con->id,
					'title' => $con->title,
					'slug' => $con->slug,
					'series' => $con->series,
			];
			if ($data->keys) {
				foreach ($con->api_keys->find_all() as $key) {
					$condata['public-key'] = $key->client_key;
					break;
				}
			}
			return $condata;
		}, $query->find_all()->as_array());
	}
	
}
