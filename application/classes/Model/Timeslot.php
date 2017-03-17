<?php

class Model_Timeslot extends ORM {
	
	const STATUS_SCHEUDLED = 0;
	const STATUS_CANCELLED = 1;
	
	const CACHE_TIME = 300;
	
	protected $_belongs_to = [
			'event' => [],
			'pass_requirement' => [],
	];
	
	protected $_has_many = [
			'hosts' => [ 'model' => 'User', 'through' => 'timeslot_hosts', 'far_key' => 'user_id' ],
			'host_names' => [ 'model' => 'Timeslot_Host' ],
			'locations' => [ 'model' => 'Location', 'through' => 'timeslot_locations' ],
			'tickets' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'event_id' => [],
			'pass_requirement_id' => [],
			// data fields
			'status' => [],
			'start_time' => [ 'type' => 'DateTime' ],
			'duration' => [],
			'min_attendees' => [],
			'max_attendees' => [],
			'notes_to_attendees' => [],
	];
	
	public static function validStatuses() {
		return [
				self::STATUS_SCHEUDLED,
		];
	}
	
	public static function persist(Model_Event $event, DateTime $start, $duration, $min_attendees, $max_attendees,
			$notes_to_attendees) : Model_Timeslot {
		$o = new Model_Timeslot();
		$o->event = $event;
		$o->status = self::STATUS_SCHEUDLED;
		$o->start_time = $start;
		$o->duration = $duration ?: $event->duration;
		$o->min_attendees = $min_attendees ?: $event->min_attendees;
		$o->max_attendees = $max_attendees ?: $event->max_attendees;
		$o->notes_to_attendees = $notes_to_attendees ?: $event->notes_to_attendees;
		$o->updatePassRequirements();
		try {
			return $o->save();
		} finally {
			$event->scheduled();
		}
	}

	/**
	 * Generate a query for all timeslots belonging to events in a convention
	 * @param Model_Convention $con convention to list for
	 * @param boolean $public whether to list only timeslots for public events
	 * @return ORM a model object with the query loaded
	 */
	public static function queryForConvention(Model_Convention $con, $public = false) : ORM {
		$query = (new Model_Timeslot)->cached(self::CACHE_TIME)->with('event')->where('convention_id', '=', $con->pk())->
			where('timeslot.status','IN', [ self::STATUS_SCHEUDLED ]);
		if ($public)
			$query = $query->where('event.status', 'IN', Model_Event::public_statuses());
		return $query;
	}

	/**
	 * Try to auto detect pass requirement for the timeslot
	 * @return boolean whether we've updated the timeslot fields (so it'll need saving)
	 */
	public function updatePassRequirements() {
		if (!@$this->event->convention->usePasses())
			return false;
		
		if ($this->pass_requirement->loaded())
			return false; // don't update if the field is already set

		// locate existing matching requirement
		$con = $this->event->convention;
		$constart = $con->start_date;
		$mystart = $this->start_time->getTimestamp();
		foreach ($con->pass_requirements->find_all() as $passreq) {
			$start_time = (clone $constart)->add($passreq->start_time);
			$end_time = (clone $constart)->add($passreq->end_time);
			error_log("Checking timeslot " . $this->pk() . " (".$mystart.") against passreq " . $passreq->pk() . " " . $start_time->getTimestamp() . "-" . $end_time->getTimestamp());
			if ($start_time->getTimestamp() <= $mystart && $mystart < $end_time->getTimestamp()) {
				$this->pass_requirement = $passreq;
				return true;
			}
		}
		return false; // no changes were done
	}
	
	public function validTickets() {
		return $this->tickets->where('ticket.status','in', Model_Ticket::validStatuses())->cached(self::CACHE_TIME)->find_all();
	}

	public function get($column) {
		switch ($column) {
			case 'end_time':
				return (clone $this->start_time)->add(new DateInterval("PT".$this->duration."M"));
			case 'available_tickets':
				return $this->max_attendees - Model_Ticket::countForTimeslot($this);
			case 'status_text':
				switch($this->status) {
					case self::STATUS_SCHEUDLED: return 'scheduled';
					case self::STATUS_CANCELLED: return 'cancelled';
					default: return 'unknown';
				}
			default: return parent::get($column);
		}
	}
	
	public function addHost(Model_User $user, $name = null) {
		if ($this->has('hosts', $user))
			return; // don't add multiple users
		if (!$name)
			$name = $user->name; // make sure we always store a name to make is easier for readers
		Model_Timeslot_Host::persist($this, $user, $name);
	}
	
	public function deleteCancelledTickets() {
		foreach ($this->tickets->where('status','=',Model_Ticket::STATUS_CANCELLED)->find_all() as $ticket)
			$ticket->delete();
	}
	
	/**
	 * Check if this time slot conflicts with the time range specified by the arguments
	 * @param DateTime $start Start time to check against
	 * @param DateTime $end End time to check against
	 */
	public function conflicts(DateTime $start, DateTime $end) {
		$beforeend = (clone $end)->sub(new DateInterval("PT1S"));
		return !(
				($this->end_time->diff($start)->invert == 0) // my end is <= than their start
				or
				($this->start_time->diff($beforeend)->invert == 1) // my start is >= than their time
				);
	}
	
	public function cancel() {
		$this->status = self::STATUS_CANCELLED;
		$this->save();
	}
	
	/**
	 * Special for_json used by Timeslots REST API, to prevent infinite recursions
	 * @return array
	 */
	public function for_json_with_locations() {
		return array_merge(
				$this->for_json(),
				[ 'locations' => self::result_for_json($this->locations->cached(self::CACHE_TIME)->find_all()) ]
				);
	}
	
	public function for_json() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return in_array($key, [
					'id', 'duration', 'min-attendees', 'max-attendees', 'notes-to-attendees',
			]);
		},ARRAY_FILTER_USE_KEY),[
				'event' => $this->event->for_json(),
				'start' => $this->start_time->format(DateTime::ATOM),
				'end' => $this->end_time->format(DateTime::ATOM),
				'hosts' => self::result_for_json($this->host_names->cached(self::CACHE_TIME)->find_all()),
				'available_tickets' => $this->available_tickets,
				'locations' => self::result_for_json($this->locations->cached(self::CACHE_TIME)->find_all()),
				'pass_requirement' => $this->pass_requirement->loaded() ? $this->pass_requirement->for_json() : null,
				'status' => $this->status_text,
		]);
	}
}
