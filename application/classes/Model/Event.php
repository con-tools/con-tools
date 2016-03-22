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
			'price' => [],
			'status' => [],
			'requires_registration' => [],
			'duration' => [],
			'min_attendees' => [],
			'max_attendees' => [],
			'notes_to_staff' => [],
			'notes_to_attendees' => [],
			'scheduling_constraints' => [],
			'logistical_requirements' => [],
			'custom_data' => [],
	];
	
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
			string $title, string $teaser, string $description, $registration_required,
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
			default:
				return parent::get($column);
		}
	}
	
	/**
	 * Add a generic tag to the event
	 * @param Model_Event_Tag_Value $tag Tag to apply to the event
	 * @return Model_Event the event object
	 */
	public function tag(Model_Event_Tag_Value $tag) : Model_Event {
		return $this->add('event_tag_values', $tag);
	}
	
	public function isPublic() {
		return $this->status == self::STATUS_APPROVED;
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
	
	/**
	 * {@inheritDoc}
	 * @see ORM::for_json()
	 */
	public function for_json() {
		$ar = parent::for_json();
		unset($ar['user-id']);
		unset($ar['staff-contact-id']);
		$ar['user'] = $this->user->for_public_json();
		$ar['staff-contact'] = $this->staff_contact->loaded() ? $this->staff_contact->for_public_json() : null;
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
