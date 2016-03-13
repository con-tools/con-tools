<?php
class Controller_Entities_Events extends Api_Rest_Controller {
	
	private function create(Model_Convention $con, Model_User $user, stdClass $data) {
		$tag_event_type = Model_Event_Tag_Type::generate('event_type');
		$value_event_type = Model_Event_Tag_Value::generate($tag_event_type, $data->event_type);
		
		$tag_age_requirement = Model_Event_Tag_Type::generate('age_requirement');
		$value_age_requirement = Model_Event_Tag_Value::generate($tag_age_requirement, $data->{'age-requirement'});
		
		try {
			return Model_Event::persist($con, $user, $data->title, $value_event_type, $data->teaser, $data->description, 
					$data->{'requires-registration'}, $data->duration, $data->{'min-attendees'},
					$data->{'max-attendees'}, $data->{'notes-to-staff'}, $data->{'logistical-requirements'},
					$data->{'notes-to-attendees'}, $data->{'scheduling-constraints'});
		} catch (Exception $e) {
			throw $e;
		}
	}
}