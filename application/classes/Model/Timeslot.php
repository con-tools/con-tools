<?php

class Model_Timeslot extends ORM {
	
	protected $_belongs_to = [
			'event' => [],
	];
	
	protected $_has_many = [
			'hosts' => [ 'model' => 'User', 'through' => 'timeslot_hosts', 'far_key' => 'user_id' ],
			'locations' => [ 'model' => 'Location', 'through' => 'timeslot_locations' ],
			'tickets' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'event_id' => [],
			// data fields
			'start_time' => [ 'type' => 'DateTime' ],
			'duration' => [],
			'min_attendees' => [],
			'max_attendees' => [],
			'notes_to_attendees' => [],
	];
	
	public static function persist(Model_Event $event, DateTime $start, $duration, $min_attendees, $max_attendees,
			$notes_to_attendees) : Model_Timeslot {
		$o = new Model_Timeslot();
		$o->event = $event;
		$o->start_time = $start;
		$o->duration = $duration ?: $event->duration;
		$o->min_attendees = $min_attendees ?: $event->min_attendees;
		$o->max_attendees = $max_attendees ?: $event->max_attendees;
		$o->notes_to_attendees = $notes_to_attendees ?: $event->notes_to_attendees;
		return $o->save();
	}
	
	/**
	 * Special for_json used by Timeslots REST API, to prevent infinite recursions
	 * @return array
	 */
	public function for_json_with_locations() {
		return array_merge(
				$this->for_json(),
				[ 'locations' => self::result_for_json($this->locations->find_all()) ]
				);
	}
	
	public function for_json() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return in_array($key, [
					'id', 'duration', 'min-attendees', 'max-attendees', 'notes-to-attendees'
			]);
		},ARRAY_FILTER_USE_KEY),[
				'event' => $this->event->for_json(),
				'start' => $this->start_time->format(DateTime::ATOM),
				'hosts' => self::result_for_json($this->hosts->find_all()),
		]);
	}
}
