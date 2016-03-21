<?php
class Controller_Entities_Events extends Api_Rest_Controller {
	
	protected function create(Model_Convention $con, Model_User $user) {
		if (is_null($this->user)) throw new Api_Exception_Unauthorized();
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
		if ($o->isPublic())
			return $o->for_json();
		if (!is_null($this->user) and ($this->convention->isManager($this->user) or $o->user_id == $this->user->pk))
			return $o->for_json();
		return null;
	}
	
	protected function update(Model_Convention $con, Model_User $user, $id) {}
	protected function delete(Model_Convention $con, Model_User $user, $id) {}
}