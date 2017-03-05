<?php
class Controller_Entities_Timeslots extends Api_Rest_Controller {
	
	protected function create() {
		if (!$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to create time slots!");
		$data = $this->input();
		$event = new Model_Event($data->event);
		if (!$event->loaded())
			throw new Api_Exception_InvalidInput($this, "Invalid event specified '{$data->event}'");
				
		$start = $this->parseDateTime($data->start);
		if (!$start)
			throw new Api_Exception_InvalidInput($this, "Invalid start time specified");
		
		// verify locations
		$locations = $this->getLocationList($data->fetch('locations'));
		if (empty($locations))
			throw new Api_Exception_InvalidInput($this, "Please specify at least one location");
		
		// check that we don't input conflicting time slots
		$duration = $data->duration ?: $event->duration;
		$endtime = (clone $start)->add(new DateInterval("PT{$duration}M"));
		$conflicting_slots = [];
		foreach ($locations as $location)
			$location->isAvailable($start, $endtime, $conflicting_slots); // I'm not checking the status now, I'll review $conflicting_slots later
		if (!empty($conflicting_slots)) {
				throw new Api_Exception_InvalidInput($this, "Location {$location->title} is not available between ".
						$start->format(DateTime::ATOM)." and ".$endtime->format(DateTime::ATOM)."!", ['timeslots' => $conflicting_slots]);
		}
				
		// verify hosts
		$hosts = $this->getHostList($data->fetch('hosts'));
		if (is_array($hosts) and empty($hosts))
			throw new Api_Exception_InvalidInput($this, "Please specify a valid host");
		
		$timeslot = Model_Timeslot::persist($event, $start, $data->duration, $data->min_attendees, $data->max_attendees,
				$data->notes_to_attendees);
		if ($this->convention->usePasses() && $timeslot->updatePassRequirements())
			$timeslot->save();
		
		foreach ($locations as $location)
			$timeslot->add('locations', $location);
		
		if (is_array($hosts)) {
			foreach ($hosts as $host)
				$timeslot->addHost($host['user'],$host['name']);
		} else {
			$timeslot->addHost($timeslot->event->user);
		}
		
		return $timeslot->for_json_with_locations();
	}
	
	protected function retrieve($id) {
		// expire reserved tickets before listing timeslots, so we can show correct availability
		$this->convention->expireReservedTickets();
	
		$timeslot = new Model_Timeslot($id);
		if ($timeslot->loaded())
			return $timeslot->for_json_with_locations();
		return null;
	}
	
	protected function update($id) {
		if (!$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to update time slots!");
		
		$timeslot = new Model_Timeslot($id);
		if (!$timeslot->loaded())
			throw new Api_Exception_InvalidInput($this, "Please specify a valid time slot");
		$data = $this->input();
		
		// verify locations
		$locations = $this->getLocationList($data->fetch('locations'));
		// verify removed locations
		$remlocations = $this->getLocationList($data->fetch('remove-locations'));
		// verify hosts
		$hosts = $this->getHostList($data->fetch('hosts')) ?: [];
		// verify removed hosts
		$remhosts = $this->getHostList($data->fetch('remove-hosts')) ?: [];
		
		// start transaction
		Database::instance()->begin();
		
		try {
			// update locations
			foreach ($locations as $location)
				if (!$timeslot->has('locations', $location))
					$timeslot->add('locations', $location);
			foreach ($remlocations as $location)
				$timeslot->remove('locations', $location);
			if ($timeslot->locations->count_all() < 1)
				throw new Api_Exception_InvalidInput($this, "Cannot remove all locations!");
			
			// update hosts
			foreach ($hosts as $host)
				$timeslot->addHost($host['user'], $host['name']);
			foreach ($remhosts as $host)
				$timeslot->remove('hosts', $host);
			if ($timeslot->hosts->count_all() < 1)
				$timeslot->add('hosts', $timeslot->event->user);
			
			// update time slot fields
			$start = $this->parseDateTime($data->start);
			$start->setTime($start->format("H"), $start->format("i"), 0); // reset seconds, in case something weird is going on with the UI
			if ($start)
				$timeslot->start_time = $start;
			if (is_numeric($data->duration))
				$timeslot->duration = (int)$data->duration;
			if (is_numeric($data->min_attendees))
				$timeslot->min_attendees = (int)$data->min_attendees;
			if (is_numeric($data->max_attendees))
				$timeslot->max_attendees = (int)$data->max_attendees;
			if ($data->isset('notes-to-attendees'))
				$timeslot->notes_to_attendees = $data->fetch('notes-to-attendees');
			$timeslot->save();
		} catch (Throwable $e) {
			Database::instance()->rollback();
			throw $e;
		}
		Database::instance()->commit();
		return $timeslot->for_json_with_locations();
	}
	
	protected function delete($id) {
		if (!$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to delete time slots!");
		$timeslot = new Model_Timeslot($id);
		if (!$timeslot->loaded())
			return true;
		
		if (count($timeslot->validTickets()->as_array()))
			throw new Api_Exception_InvalidInput($this, "Not allowed to delete time slots with valid tickets!");
		$timeslot->cancel();
		return true;
	}
	
	protected function catalog() {
		// expire reserved tickets before listing timeslots, so we can show correct availability
		$this->convention->expireReservedTickets();
		
		$data = $this->input();
		
		if ($data->update_passes) {
			foreach ($this->convention->getTimeSlots() as $timeslot ) {
				if ($timeslot->updatePassRequirements())
					$timeslot->save();
			}
		}
		
		$filters = [];
		if ($data->by_event)
			$filters['event_id'] = $data->by_event;
		if ($data->by_event_status)
			$filters['status'] = $data->by_event_status;
		if ($data->by_host)
			$filters['host'] = $data->by_host == 'self' ? $this->user->pk() : $this->loadUserByIdOrEmail($data->by_host)->pk();
		
		if ($this->systemAccessAllowed())
			$catalog = ORM::result_for_json($this->convention->getTimeSlots($filters), 'for_json_with_locations');
		else // if not specifically authorized, get public list
			$catalog = ORM::result_for_json($this->convention->getPublicTimeSlots($filters), 'for_json_with_locations');
		// check if we need to filter by tags
		foreach ($data->getFields() as $key => $value)
			if (strpos($key, 'by_tag:') === 0) {
				$tagname = explode(':', $key, 2)[1];
				$catalog = array_values(array_filter($catalog, function($timeslot) use($tagname, $value) {
					$tagvalue = @$timeslot['event']['tags'][$tagname];
					if (is_array($tagvalue))
						return in_array($value, $tagvalue);
					return $tagvalue == $value;
				}));
			}
		return $catalog;
	}
	
	private function getHostList($data) {
		if (!is_array($data))
			return null; // no host list specified has a special meaning
		return array_map(function($user){
			$obj = $this->loadUserByIdOrEmail(@$user['id'], @$user['email']);
			return [
					'user' => $obj,
					'name' => @$user['name'] ?: $user->name
			];
		}, $data);
	}
	
	private function getLocationList($data) {
		if (!is_array($data))
			return [];
		
		return array_map(function($slug){
			try {
				return Model_Location::byConventionSlug($this->convention, $slug);
			} catch (Model_Exception_NotFound $e) {
				throw new Api_Exception_InvalidInput($this, "Invalid location specified: '$slug'");
			}
		}, $data);
	}
}
