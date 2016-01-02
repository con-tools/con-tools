<?php
class Controller_Entities_Events extends Api_Rest_Controller {
	
	private function create(Model_Convention $con, Model_User $user, stdClass $data) {
		// Create model instance
		$event = new Model_Event();
		//Add Data
		$event->user_id = is_null($data->user_id)?$user->pk():$data->user_id;
		$event->convention = $con;
		$event->title = $data->title;
		$event->teaser = $data->teaser;
		$event->description = $data->description;
		$event->duration = $data->duration;
		$event->min_attendees = $data->min_attendees;
		$event->max_attendees = $data->max_attendees;
		$event->notes_to_staff = $data->notes_to_staff;
		$event->notes_to_attendees = $data->notes_to_attendees;
		$event->scheduling_constraints = $data->scheduling_constraints;
		//Add data arrays
		$event->event_tags = $data->event_tags;
		$event->tags = $data->tags;
		$event->media = $data->media;
		//Create the event
		$event->save();
		return $event;
	}
}