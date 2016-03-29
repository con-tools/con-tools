<?php

class Controller_Entities_Ticketse extends Api_Rest_Controller {
	
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
	
}
