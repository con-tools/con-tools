<?php

class Model_Event extends ORM {
	
	const STATUS_SUBMITTED = 0;
	const STATUS_HAS_TEASER = 1;
	const STATUS_CONTENT_APPROVED = 2;
	const STATUS_LOGISTICS_APPROVED = 3;
	const STATUS_SCHEDULED = 4;
	const STATUS_APPROVED = 5;
	const STATUS_CANCELLED = 6;
	
	protected $_belongs_to = [
			'convention' => [],
			'user' => [],
			'staff_contact' => [ 'model' => 'user', 'foreign_key' => 'staff_contact_id' ],
	];
	
	protected $_has_many = [
			'timeslots' => [],
			'event_tag_values' => [ 'through' => 'event_tags' ],
			'crm_issues' => [],
			'media' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'user_id' => [],
			'staff_contact_id' => [],
			'convention_id' => [],
			// data fields
			'title' => [],
			'teaser' => [],
			'description' => [],
			'created_time' => [ 'type' => 'DateTime' ],
			'updated_time' => [ 'type' => 'DateTime' ],
			'price' => [],
			'status' => [],
			'requires_registration' => [ 'type' => 'boolean' ],
			'duration' => [],
			'min_attendees' => [],
			'max_attendees' => [],
			'notes_to_staff' => [],
			'notes_to_attendees' => [],
			'scheduling_constraints' => [],
			'logistical_requirements' => [],
			'custom_data' => [],
	];
	
	public static function public_statuses() {
		return [
				self::STATUS_SCHEDULED,
				self::STATUS_APPROVED,
		];
	}
	
	/**
	 * Generate a new event in the system
	 * @param Model_Convention $con Convention where this event takes place
	 * @param Model_User $user Event contact person and owner
	 * @param string $title Title of the event
	 * @param string $teaser Teaser for the event
	 * @param string $description Long description of the event (Markdown is fine)
	 * @param boolean $registration_required whether registration is required (or its a free for all)
	 * @param int $duration Expected duration of the event in minutes
	 * @param int $min_attendees Minimal number of attendees required for the event (for it to open)
	 * @param int $max_attendees Maximum number of attendees allowed
	 * @param string $notes_to_staff Notes for the staff contact person
	 * @param string $logistical_requirements Requirements for the logistic team
	 * @param string $notes_to_attendees Notes to show potential attendees
	 * @param string $scheduling_constraints Note to scheduling staff
	 * @param unknown $custom_data Custom convention-specific arbitrary data
	 */
	public static function persist(Model_Convention $con, Model_User $user,
			string $title, string $teaser, $description, $registration_required,
			int $duration, $min_attendees, $max_attendees,
			$notes_to_staff, $logistical_requirements, $notes_to_attendees,
			$scheduling_constraints, $custom_data) : Model_Event {
		$o = new Model_Event();
		$o->user = $user;
		$o->convention = $con;
		$o->title = $title;
		$o->teaser = $teaser;
		$o->description = $description;
		$o->requires_registration = $registration_required ? true : false;
		$o->duration  = $duration;
		$o->min_attendees = $min_attendees ?: 1;
		$o->max_attendees = $max_attendees;
		$o->notes_to_staff = $notes_to_staff;
		$o->notes_to_attendees = $notes_to_attendees;
		$o->scheduling_constraints = $scheduling_constraints;
		$o->logistical_requirements = $logistical_requirements;
		$o->custom_data = json_encode($custom_data);
		$o->status = self::STATUS_SUBMITTED;
		$o->save();
		return $o;
	}
	
	/**
	 * {@inheritDoc}
	 * @see Kohana_ORM::get()
	 */
	public function get($column) {
		switch ($column) {
			case 'custom_data':
				return json_decode(parent::get($column));
			case 'status_text':
				switch ($this->status) {
					case self::STATUS_SUBMITTED: return 'submitted';
					case self::STATUS_HAS_TEASER: return 'has teaser';
					case self::STATUS_CONTENT_APPROVED: return 'content approved';
					case self::STATUS_LOGISTICS_APPROVED: return 'logistics approved';
					case self::STATUS_SCHEDULED: return 'scheduled';
					case self::STATUS_APPROVED: return 'approved';
					case self::STATUS_CANCELLED: return 'cancelled';
				}
			default:
				return parent::get($column);
		}
	}
	
	public function set($column, $value) {
		if (empty($this->_object_name)) // object initialization, don't override anything yet
			return parent::set($column, $value);
		
		switch ($column) {
			// set some fields also for timeslots
			case 'min_attendees':
			case 'max_attendees':
			case 'duration':
			case 'notes_to_attendees':
				$this->updateTimeslots($column, $value);
				return parent::set($column, $value);
			case 'custom_data':
				return parent::set($column, json_encode($value));
			default: return parent::set($column, $value);
		}
	}
	
	/**
	 * Retrieve all event tags of the specified type
	 * @param Model_Event_Tag_Type $type type of tag to retrieve
	 */
	public function getTags(Model_Event_Tag_Type $type) {
		return $this->event_tag_values->where('event_tag_type_id', '=', $type->pk())->find_all();
	}
	
	/**
	 * Add a system tag to the event
	 * @param Model_Event_Tag_Value $tag Tag to apply to the event
	 * @return Model_Event the event object
	 */
	public function tag(Model_Event_Tag_Value $tag) : Model_Event {
		if ($tag->getType()->requiredOne()) { // when adding a "required one" tag, replace existing
			foreach ($this->getTags($tag->getType()) as $evtag)
				$this->remove('event_tag_values', $evtag);
		}
		if (!$this->has('event_tag_values', $tag))
			$this->add('event_tag_values', $tag);
		return $this;
	}
	
	/**
	 * Update specified values in all timeslots for this event
	 * @param string $column field name to update
	 * @param mixed $value value to set
	 */
	public function updateTimeslots($column, $value) {
		foreach ($this->timeslots->find_all() as $timeslot) {
			$timeslot->set($column, $value);
			$timeslot->save();
		}
	}
	
	/**
	 * Remove a system tag from the event, if possible to maintain tag requirements
	 * @param Model_Event_Tag_Value $tag Tag to remove
	 * @throws InvalidArgumentException in case the removal will break tag requirement specification
	 */
	public function untag(Model_Event_Tag_Value $tag) {
		// check if we are allowed to untag
		if ($tag->getType()->requiredOne() && $this->has('event_tag_values',$tag))
			throw new InvalidArgumentException("Not allowed to remove required tag '{$tag->type}:{$tag->title}' without providing a replacement");
		if ($tag->getType()->requiredMany()) {
			$curtags = $this->getTags($tag->getType());
			if (count($curtags) == 1 && $curtags[0]->pk() == $tag->pk())
				throw new InvalidArgumentException("Not allowed to remove the last tag of a 'required' type");
		}
		return $this->remove('event_tag_values', $tag);
	}
	
	public function isPublic() {
		return $this->status == self::STATUS_APPROVED;
	}
	
	public function scheduled() {
		if ($this->status >= self::STATUS_SCHEDULED)
			return;
		$this->status = self::STATUS_SCHEDULED;
		return $this->save();
	}
	
	/**
	 * Cancel the event and don't let it show anywhere
	 * @return Model_Event
	 */
	public function cancel() : Model_Event {
		$this->status = self::STATUS_CANCELLED;
		$this->save();
		return $this;
	}
	
	public function for_json_no_tags() {
		$ar = array_filter(parent::for_json(), function($key){
			return in_array($key, ['id', 'title', 'teaser', 'description', 'price', 'requires-registration', 'duration',
					'min-attendees', 'max-attendees', 'notes-to-staff', 'logistical-requirements', 'notes-to-attendees',
					'scheduling-constraints', 'custom-data', 'status', 'created-time', 'updated-time' ]);
		},ARRAY_FILTER_USE_KEY);
			$ar['status-text'] = $this->get('status_text');
			$ar['user'] = $this->user->for_json();
			$ar['staff-contact'] = $this->staff_contact->loaded() ? $this->staff_contact->for_json() : null;
			return $ar;
	}
	
	/**
	 * {@inheritDoc}
	 * @see ORM::for_json()
	 */
	public function for_json() {
		$ar = $this->for_json_no_tags();
		$ar['status-text'] = $this->get('status_text');
		$ar['user'] = $this->user->for_json();
		$ar['staff-contact'] = $this->staff_contact->loaded() ? $this->staff_contact->for_json() : null;
		$ar['tags'] = [];
		foreach ($this->event_tag_values->find_all() as $tag_value) {
			if ($tag_value->event_tag_type->requiredOne())
				$ar['tags'][$tag_value->type] = $tag_value->title;
			else
				$ar['tags'][$tag_value->type][] = $tag_value->title;
		}
		return $ar;
	}
	
}
