<?php
class Controller_Entities_Events extends Api_Rest_Controller {
	
	private function generateTags($tagspec) {
		if (!is_array($tagspec)) // no array, no fun
			return [];
		
		$tags = [];
		foreach ($tagspec as $key => $values) {
			$tag_type = Model_Event_Tag_Type::generate($this->convention, $key, true, is_array($values));
			// check for consistency
			if ($tag_type->requiredOne() and is_array($values))
				throw new Api_Exception_InvalidInput($this, "Tag '$key' does not support multiple values!");
			if (!is_array($values))
				$values = [ $values ];
			foreach ($values as $value) {
				$tags[] = Model_Event_Tag_Value::generate($tag_type, $value);
			}
		}
		return $tags;
	}
	
	protected function create() {
		if (is_null($this->user))
			throw new Api_Exception_Unauthorized($this, "Must be logged in!");
		$data = $this->input();
		
		// check if a convention manager wants to set a different event owner
		if ($data->isset('user')) {
			if (!$this->convention->isManager($this->user))
				throw new Api_Exception_Unauthorized($this, "{$this->user->email} Not authorized to set event owner to another user");
			$owner = $this->loadUserByIdOrEmail($data->fetch('user.id'), $data->fetch('user.email'));
		} else
			$owner = $this->user;
		
		try {
			$created_time = null;
			if ($data->created_time) {
				$created_time = $this->parseDateTime($data->created_time);
			}
			
			// try to figure out tags ahead of generating the event - if the user messed up the tag spec, we
			// should not create the event
			$typed_tags = $this->generateTags($data->tags);
			
			if (!$data->title)
				throw new Api_Exception_InvalidInput($this,'Please provide "title" field');
			if (!$data->teaser)
				throw new Api_Exception_InvalidInput($this,'Please provide "teaser" field');
				
			$ev = Model_Event::persist($this->convention, $owner, $data->title, $data->teaser, $data->description,
					$data->requires_registration, $data->duration, $data->min_attendees,
					$data->max_attendees, $data->notes_to_staff, $data->logistical_requirements,
					$data->notes_to_attendees, $data->scheduling_constraints, $data->data);
			if (!is_null($created_time)) {
				$ev->created_time = $created_time;
				$ev->saved();
			}
			foreach ($typed_tags as $tag)
				$ev->tag($tag);
			return $ev;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	protected function retrieve($id) {
		$o = new Model_Event($id);
		if (!$o->loaded())
			throw new Model_Exception_NotFound();
		if ($o->convention_id != $this->convention->pk())
			throw new Api_Exception_Unauthorized($this, "Incorrect convention selected for event {$o->pk()} ({$o->convention->pk()} != {$o->convention_id})"); // can't hack around convention keys
		if ($o->isPublic())
			return $o->for_json();
		if (!is_null($this->user) and ($this->convention->isManager($this->user) or $o->user_id == $this->user->pk))
			return $o->for_json();
		return null;
	}
	
	protected function update($id) {
		$data = $this->input();
		if (is_null($this->user))
			throw new Api_Exception_Unauthorized($this, "Must be logged in!");
		$o = new Model_Event($id);
		if (!$o->loaded())
			throw new Api_Exception_InvalidInput($this, "Event not found");
		if ($o->convention_id != $this->convention->pk())
			throw new Api_Exception_Unauthorized($this, "Incorrect convention selected ({$o->convention_id} != {$this->convention->pk()})!"); // can't hack around convention keys
		if ($this->convention->isManager($this->user)) { // allow to change all fields
			foreach ($data->getFields([
					'title', 'teaser', 'description', 'duration', 'min_attendees', 'max_attendees',
					'notes_to_staff', 'logistical_requirements', 'notes_to_attendees', 'scheduling_constraints', 'data',
					'status', 'price', 'requires_registration']) as $column => $value)
				$o->set($column, $value);
			if ($data->staff_contact) { // load staff contact
				$o->staff_contact = Model_User::byEmail($data->staff_contact);
			}
			$o->save();
			// add tags to the event
			if ($data->tags) {
				foreach ($this->generateTags($data->tags) as $tag) {
					$o->tag($tag);
				}
			}
			// remove tags from event
			if ($data->remove_tags) {
				try {
					foreach ($this->generateTags($data->remove_tags) as $tag) {
						$o->untag($tag);
					}
				} catch (InvalidArgumentException $e) {
					throw new Api_Exception_InvalidInput($this, $e->getMessage());
				}
			}
			return $o->for_json();
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
		if ($this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to cancel events!");
		$o = new Model_Event($id);
		if (!$o->loaded())
			return true; // that's fine
		if ($o->convention_id != $this->convention->pk())
			throw new Api_Exception_Unauthorized($this, "Incorrect convention selected!"); // can't hack around convention keys
		$o->delete();
		return true;
	}

	protected function catalog() {
		if (!is_null($this->user)) {
			if ($this->convention->isManager($this->user)) // admins have full access
				return ORM::result_for_json($this->convention->events->find_all());
			else // return public and owned events
				return ORM::result_for_json($this->convention->events->
					where('status', '=', Model_Event::STATUS_APPROVED)->
					or_where('user_id', '=', $this->user->pk())->find_all());
		}
		
		// no user - return only public events
		return ORM::result_for_json($this->convention->getPublicEvents());
	}
}