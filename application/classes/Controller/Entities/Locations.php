<?php
class Controller_Entities_Locations extends Api_Rest_Controller {
	
	protected function create() {
		if (!$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to create locations!");
		$data = $this->input();
		if (!$data->title)
			throw new Api_Exception_InvalidInput($this, "Please specify title!");
		if (!$data->max_attendees)
			throw new Api_Exception_InvalidInput($this, "Please specify max_attendees!");
		try {
			$loc = Model_Location::persist($this->convention, $data->title, $data->area, $data->max_attendees, $data->slug);
			return $loc->for_json_with_timeslots();
		} catch (Api_Exception_Duplicate $e) {
			$e->setControll($this);
			throw $e;
		}
	}
	
	protected function retrieve($id) {
		try {
			return Model_Location::byConventionSlug($this->convention, $id)->for_json_with_timeslots();
		} catch (Model_Exception_NotFound $e) {
			return null;
		}
	}
	
	protected function update($id) {
		if (!$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to update locations!");
		$data = $this->input();
		try {
			$o = Model_Location::bySlug($this->convention, $id);
			if ($data->title)
				$o->title = $data->title;
			if ($data->area)
				$o->area = $data->area;
			if ($data->max_attendees)
				$o->max_attendees = $data->max_attendees;
			return $o->for_json_with_timeslots();
		} catch (Model_Exception_NotFound $e) {
			throw new Api_Exception_InvalidInput($this, "Location {$id} not found");
		}
	}
	
	protected function delete($id) {
		if (!$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to delete locations!");
		try {
			$loc = Model_Location::bySlug($this->convention, $id);
			try {
				$loc->delete();
			} catch (Database_Exception $e) {
				throw new Api_Exception_InvalidInput($this, "Location cannot be deleted as it is used by the existing time slots: ".
						join(', ', array_map(function(Model_Timeslot $ts){
							return $ts->pk();
						}, $loc->getTimeslots()->as_array()))
						);
			}
		} catch (Model_Exception_NotFound $e) {} // does not exist, so its ok.
		return true;
	}
	
	protected function catalog() {
		return ORM::result_for_json($this->convention->locations->find_all(), 'for_json_with_timeslots');
	}
	
}
