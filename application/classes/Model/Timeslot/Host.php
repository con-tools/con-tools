<?php
class Model_Timeslot_Host extends ORM {

	protected $_belongs_to = [
		'user' => [],
		'timeslot' => []
	];

	protected $_columns = [
		'id' => [],
		// foreign keys
		'user_id' => [],
		'timeslot_id' => [],
		// fields
		'name' => []
	];

	public static function queryForConvention(Model_Convention $con, $public = false): ORM {
		$query = (new Model_Timeslot_Host())->with('timeslot:event')->where('timeslot:event.convention_id', '=', $con->pk());
		if ($public)
			$query = $query->where('timeslot:event.status', 'IN', Model_Event::public_statuses());
		return $query;
	}

	public static function persist(Model_Timeslot $timeslot, Model_User $user, $name): Model_Timeslot_Host {
		$o = new Model_Timeslot_Host();
		$o->user = $user;
		$o->timeslot = $timeslot;
		$o->name = $name;
		$o->save();
		return $o;
	}
	
	public static function forTimeslotHost(Model_Timeslot $timeslot, Model_User $user) : Model_Timeslot_Host {
		return (new Model_Timeslot_Host())
			->where('timeslot_id', '=', $timeslot->pk())
			->where('user_id', '=', $user->pk())
			->find();
	}
	
	public function for_json() {
		return array_merge($this->user->for_json(), [
			'id' => $this->user->pk(),
			'name' => $this->name ?: $this->user->name
		]);
	}

}
