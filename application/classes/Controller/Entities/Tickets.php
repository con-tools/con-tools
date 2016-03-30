<?php

class Controller_Entities_Tickets extends Api_Rest_Controller {
	
	public function create() {
		$data = $this->input();
		$timeslot = new Model_Timeslot($data->timeslot);
		if (!$timeslot->loaded())
			throw new Api_Exception_InvalidInput($this, "No time slot specified for the sell");
		$amount = $data->amount ?: 1;
		if ($timeslot->available_tickets < $amount)
			throw new Api_Exception_Duplicate($this, "Not enough tickets left");
		// start the reservation
		Database::instance()->begin();
		$ticket = Model_Ticket::persist($timeslot, $this->getValidUser());
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
		if ($ticket->loaded() && ($ticket->user == $this->getValidUser() || $this->systemAccessAllowed()))
			return $ticket->for_json();
		throw new Api_Exception_InvalidInput($this, "No valid tickets found to display");
	}
	
	public function update($id) {
		// allow user to update the amount of tickets they want to buy, if there are tickets available
		$amount = $this->input()->amount;
		if (!is_numeric($amount))
			throw new Api_Exception_InvalidInput($this, "Amount must be a numerical value");
		$ticket = new Model_Ticket($id);
		if (!$ticket->loaded() || $ticket->user != $this->getValidUser())
			throw new Api_Exception_InvalidInput($this, "No ticket found");
		Database::instance()->begin();
		$ticket->setAmount($amount);
		
		if ($ticket->amount <= 0) { // cancel the ticket
			$ticket->cancel("user-deleted");
			Database::instance()->commit();
			return $ticket->for_json();
		}

		$ticket->save();
		// check if we're still OK
		if ($ticket->timeslot->available_tickets < 0) {
			// cancel the transaction
			Database::instance()->rollback();
			throw new Api_Exception_InvalidInput($this, "No more tickets left");
		}
		
		Database::instance()->commit();
		return $ticket->for_json();
	}
	
	public function delete($id) {
		if (!$this->systemAccessAllowed())
			throw new Api_Exception_Unauthorized($this, "Not authorized to delete tickets");
		$ticket = new Model_Ticket((int)$id);
		if (!$ticket->loaded())
			throw new Api_Exception_InvalidInput($this, "No ticket found for '$id'");
		if ($ticket->isAuthorized())
			throw new Api_Exception_InvalidInput($this, "Can't delete authorized tickets, cancel it first");
		$ticket->delete();
		return true;
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
			$filters['user_id'] = $this->getValidUser()->pk();
		}
		
		if ($data->by_event)
			$filters['event_id'] = $data->by_event;
		if ($data->by_timeslot)
			$filters['timeslot_id'] = $data->by_timeslot;
		if ($data->is_valid)
			$filters['valid'] = 1;
		return ORM::result_for_json($this->convention->getTickets($filters), 'for_json');
	}
	
	private function getValidUser() {
		if ($this->user) // user authenticated themselves - fine
			return $this->user;
		if ($this->convention->isAuthorized() and $this->input()->user)
			return $this->loadUserByIdOrEmail($this->input()->user, $this->input()->user);
		throw new Api_Exception_InvalidInput($this, "User must be authenticated or specified by an authorized convention");
	}
	
}
