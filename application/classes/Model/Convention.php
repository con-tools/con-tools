<?php

class Model_Convention extends ORM {
	
	protected $_has_many = [
			'organizers' => [],
			'events' => [],
			'locations' => [],
			'event_tags_types' => [],
			'crm_queues' => [],
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
	
}
