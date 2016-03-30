<?php

class Controller_Entities_Tickets extends Api_Rest_Controller {
	
	public function create() {
		if (!$this->user)
			throw new Api_Exception_Unauthorized($this, "You must be logged in to purchase a ticket");
		$data = $this->input();
		$timeslot = new Model_Timeslot($data->timeslot);
		if (!$timeslot->loaded())
			throw new Api_Exception_InvalidInput($this, "No time slot specified for the sell");
		$amount = $data->amount ?: 1;
		if ($timeslot->available_tickets < $amount)
			throw new Api_Exception_Duplicate($this, "Not enough tickets left");
		// start the reservation
		Database::instance()->begin();
		$ticket = Model_Ticket::persist($timeslot, $this->user);
		if ($timeslot->available_tickets < 0) { // someone took our tickets first
			Database::instance()->rollback();
			throw new Api_Exception_InvalidInput($this, "Not enough tickets left");
		}
		Database::instance()->commit();
		// verify sanity after I finish the transaction
		if ($timeslot->available_tickets < 0) {
			$ticket->delete();
			throw new Api_Exception_InvalidInput($this, "Not enough tickets left");
		}
		return $ticket->for_json();
	}
	
	public function retrieve($id) {
		$ticket = new Model_Ticket($id);
		if ($ticket->loaded() && ($ticket->user == $this->user || $this->systemAccessAllowed()))
			return $ticket->for_json();
		throw new Api_Exception_InvalidInput($this, "No valid tickets found to display");
	}
	
	public function update($id) {
		// TODO: do stuff
	}
	
	public function delete($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not authorized to delete tickets");
		$ticket = new Model_Ticket($id);
		if ($ticket->loaded())
			throw new Api_Exception_InvalidInput($this, "No ticket found");
		if ($ticket->isAuthorized())
			throw new Api_Exception_InvalidInput($this, "Can't delete authorized tickets, cancel it first");
		$ticket->delete();
	}
	
	public function catalog() {
		$data = $this->input();
		// two different base modes - user and admin/convention
		$filters = [];
		if ($this->systemAccessAllowed()) {
			// ehmm.. no default filters, unless the caller asked for a user filter
			if ($data->by_user) {
				if (is_numeric($data->by_user))
					$filters['user_id'] = $data->by_user;
				else
					$filters['email'] = $data->by_user;
			}
		} else {
			// verify user and filter by them
			if (!$this->user)
				throw new Api_Exception_Unauthorized($this, "You must be logged in to see your tickets");
			$filters['user_id'] = $this->user->pk();
		}
		
		if ($data->by_event)
			$filters['event_id'] = $data->by_event;
		if ($data->by_timeslot)
			$filters['timeslot_id'] = $data->by_timeslot;
		return ORM::result_for_json($this->convention->getTickets($filters), 'for_json');
	}
	
}