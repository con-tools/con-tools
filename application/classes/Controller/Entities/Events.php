<?php
class Controller_Entities_Events extends Api_Rest_Controller {
	
	protected function create(Model_Convention $con, Model_User $user) {
		if (is_null($this->user)) throw new Api_Exception_Unauthorized($this);
		$data = $this->input();
		$tag_event_type = Model_Event_Tag_Type::generate($con, 'event_type');
		$event_type = Model_Event_Tag_Value::generate($tag_event_type, $data->event_type);
		
		$tag_age_requirement = Model_Event_Tag_Type::generate($con, 'age_requirement');
		$age_requirement = Model_Event_Tag_Value::generate($tag_age_requirement, $data->age_requirement);
		
		try {
			$ev = Model_Event::persist($con, $user, $data->title, $data->teaser, $data->description, 
					$data->requires_registration, $data->duration, $data->min_attendees,
					$data->max_attendees, $data->notes_to_staff, $data->logistical_requirements,
					$data->notes_to_attendees, $data->scheduling_constraints, $data->data);
			$ev->tag($event_type);
			$ev->tag($age_requirement);
			return $ev;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	protected function retrieve(Model_Convention $con, Model_User $user, $id) {
		$o = new Model_Event($id);
		if (!$o->loaded())
			throw new Model_Exception_NotFound();
		if (!$o->convention_id != $this->convention->pk())
			throw new Api_Exception_Unauthorized($this); // can't hack around convention keys
		if ($o->isPublic())
			return $o->for_json();
		if (!is_null($this->user) and ($this->convention->isManager($this->user) or $o->user_id == $this->user->pk))
			return $o->for_json();
		return null;
	}
	
	protected function update($id) {
		$data = $this->input();
		if (is_null($this->user))
			throw new Api_Exception_Unauthorized($this);
		$o = new Model_Event($id);
		if (!$o->convention_id != $this->convention->pk())
			throw new Api_Exception_Unauthorized($this); // can't hack around convention keys
		if ($this->convention->isManager($this->user)) { // allow to change all fields
			$o->update(new Validation($data->getFields([
					'title', 'teaser', 'description', 'duration', 'min_attendees', 'max_attendees',
					'notes_to_staff', 'logistical_requirements', 'notes_to_attendees', 'scheduling_constraints', 'data',
					'status', 'price', 'requires_registration'
			])));
			if ($data->staff_contact) { // load staff contact
				$o->staff_contact = Model_User::byEmail($data->staff_contact);
			}
			$o->save();
			if ($data->tags) {
				foreach ($data->tags as $key => $value) {
					$type = Model_Event_Tag_Type::generate($this->convention, $key);
					$o->tag(Model_Event_Tag_Value::generate($type, $value));
				}
			}
			return $o->as_array();
		}
		
		if ($o->user_id == $this->user->pk() and $o->status == Model_Event::STATUS_SUBMITTED) { // owner can update some fields
			foreach ($data->getFields([
					'title', 'teaser', 'description', 'duration', 'min_attendees', 'max_attendees',
					'notes_to_staff', 'logistical_requirements', 'notes_to_attendees', 'scheduling_constraints', 'data'
			]) as $field => $value)
				$o->set($field, $value);
			return $o->save()->for_json();
		}
		return null;
	}
	
	protected function delete($id) {
		if (is_null($this->user) or !$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this);
		$o = new Model_Event($id);
		if (!$o->loaded())
			throw new Model_Exception_NotFound();
		if (!$o->convention_id != $this->convention->pk())
			throw new Api_Exception_Unauthorized($this); // can't hack around convention keys
		$o->cancel();
	}

	protected function catalog() {
		if (!is_null($this->user)) {
			if ($this->convention->isManager($this->user)) // admins have full access
				return array_map(function($event){
					return $event->for_json();
				}, $this->convention->events->find_all());
			else // return public and owned events
				return array_map(function($event){ 
					return $event->for_json();
				}, $this->convention->events->
					or_where('status', '=', Model_Event::STATUS_APPROVED)->
					or_where('user_id', '=', $this->user->pk())->find_all());
		}
		
		// no user - return only public events
		return array_map(function($event){
			return $event->for_json();
		}, $convention->events->where('status', '=', Model_Event::STATUS_APPROVED)->find_all());
	}
}