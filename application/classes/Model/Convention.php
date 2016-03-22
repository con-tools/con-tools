<?php

class Model_Convention extends ORM {
	
	protected $_has_many = [
			'organizers' => [],
			'events' => [],
			'locations' => [],
			'event_tag_types' => [],
			'crm_queues' => [],
			'managers' => [],
	];
	
	protected $_columns = [
			'id' => [],
			'slug' => [],
			// data fields
			'title' => [],
			'series' => [],
			'website' => [],
			'location' => [],
			'start_date' => [ 'type' => 'DateTime' ],
			'end_date' => [ 'type' => 'DateTime' ],
	];
	
	private $client_authorized = false;
	
	public static function persist($title, $series, $website, $location, $slug = null) {
		$obj = new Model_Convention();
		$obj->title = $title;
		$obj->slug = $slug ?: self::gen_slug($title);
		$obj->series = $series;
		$obj->website = $website;
		$obj->location = $location;
		$obj->save();
		return $obj;
	}
	
	/**
	 * Retrieve a convention for a submitted API key
	 * @param Model_Api_Key|string $apikey 
	 * @return Model_Convention
	 */
	public static function byAPIKey($apikey) {
		if (!($apikey instanceof Model_Api_Key))
			$apikey = Model_Api_Key::byClientKey($apikey);
		return $apikey->convention;
	}
	
	public function generateApiKey() {
		return Model_Api_Key::persist($this);
	}

	/**
	 * Mark that the client has authorized using a convention key
	 */
	public function setAuthorized() {
		$this->client_authorized = true;
	}

	/**
	 * Check if a client has convention authorization level
	 */
	public function isAuthorized() {
		return $this->client_authorized;
	}
	
	public function isManager(Model_User $user) {
		return count($this->managers->where('user_id','=', $user->pk())->find_all()) > 0;
	}
}
