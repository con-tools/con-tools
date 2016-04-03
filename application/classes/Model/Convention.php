<?php

class Model_Convention extends ORM {
	
	protected $_has_many = [
			'organizers' => [],
			'events' => [],
			'locations' => [],
			'event_tag_types' => [],
			'crm_queues' => [],
			'managers' => [],
			'api_keys' => [],
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
			'settings' => [],
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
	
	/**
	 * Locate convention instance by its slug
	 * @param string $slug convention slug
	 */
	public static function bySlug($slug) : Model_Convention {
		$o = (new Model_Convention())->where('slug', 'like', $slug)->find();
		if (!$o->loaded())
			throw new Model_Exception_NotFound();
		return $o;
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
	
	/**
	 * Check whether the user is a manager for the convention
	 * @param Model_User|null $user user or no user to check
	 */
	public function isManager($user) {
		if ($user instanceof Model_user)
			return count($this->managers->where('user_id','=', $user->pk())->find_all()) > 0;
		return false; // not a user - not a manager
	}

	public function addManager(Model_User $user) {
		if (!$this->isManager($user))
			Model_Manager::persist($this, $user, (new Model_Role_Manager)->getRole());
	}
	
	public function removeManager(Model_User $user) {
		$manager = $this->managers->where('user_id','=',$user->pk())->find();
		if ($manager->loaded())
			$manager->delete();
	}
	
	/**
	 * Retrieve all scheduled time slots for this convention
	 * @return Database_Result listing all time slots in the convention
	 */
	public function getTimeSlots($filters = [], $public = false) : Database_Result {
		$query = Model_Timeslot::queryForConvention($this, $public);
		if (array_key_exists('host', $filters)) {
			$hosted_ts = Model_Timeslot_Host::queryForConvention($this)
				->where('timeslot_host.user_id','=',$filters['host'])
				->find_all()->as_array();
			$query->where('timeslot.id','IN', count($hosted_ts) ? array_map(
					function($tshost) { return $tshost->timeslot->pk(); }, $hosted_ts) : [0]); // fake invalid query if no timeslot_hosts
			unset($filters['host']);
		}
		foreach ($filters as $field  => $value)
			$query = $query->where($field,'=',$value);
		return $query->find_all();
	}
	
	/**
	 * Retrieve all scheduled time slots for events that are OK to be published
	 * @return Database_Result listing all time slots in the convention
	 */
	public function getPublicTimeSlots($filters = []) : Database_Result {
		return $this->getTimeSlots($filters, true);
	}

	/**
	 * Retrieve all tickets registered on a convention (including cancelled tickets)
	 * @param array $filters list of filters to add to the quer
	 * @return Database_Result list of all tickets
	 */
	public function getTickets($filters = []) : Database_Result {
		$query = Model_Ticket::queryForConvention($this);
		foreach ($filters as $field  => $value) {
			if ($field == 'valid') // don't show cancelled
				$query = $query->where('ticket.status', '<>', Model_Ticket::STATUS_CANCELLED);
			else
				$query = $query->where($field,'=',$value);
		}
		return $query->find_all();
	}
	
	public function expireReservedTickets() {
		$reservetime = @$this->get('settings')['reservation-time'];
		if (!$reservetime) return; // sanity
		// calculate oldest reserve time
		$reservetime = new DateInterval($reservetime);
		$reservetime->invert = 1;
		$last = new DateTime();
		$last->add($reservetime);
		foreach (Model_Ticket::reservedByReserveTime($last) as $ticket) {
			Logger::info("Expiring old reserve ticket " . $ticket->pk() . " for " . $ticket->user->email .
					" from " . $ticket->reserved_time->format(DateTime::ATOM));
			$ticket->cancel("internal:reservation-timeout");
		}
	}
	
	public function get($column) {
		switch ($column) {
			case 'settings':
				return json_decode(parent::get('settings'), true);
			default: return parent::get($column);
		}
	}
	
	public function getPaymentProcessor() : Payment_Processor {
		return Payment_Processor::instance($this, @$this->get('settings')['payment-processor']);
	}
	
/**
	 * Retrieve all events that have been "published"
	 * @return Database_Result listing of published events
	 */
	public function getPublicEvents() : Database_Result {
		return $this->events->where('status', '=', Model_Event::STATUS_APPROVED)->find_all();
	}
	
	public function getPublicKey() {
		foreach ($this->api_keys->find_all() as $key)
			return $key->client_key;
		return false;
	}
	
	/**
	 * Don't expose private convention settings in public convnetion view
	 * {@inheritDoc}
	 * @see ORM::for_json()
	 */
	public function for_json() {
		return array_filter(parent::for_json(),function($key){
			return $key != 'settings';
		},ARRAY_FILTER_USE_KEY);
	}
	
	/**
	 * Export private data for managers only
	 */
	public function for_private_json() {
		$ar = $this->for_json();
		foreach ($this->api_keys->find_all() as $key) {
			$ar['public-key'] = $key->client_key;
			$ar['secret-key'] = $key->client_secret;
		}
		return $ar;
	}
}
