<?php

class Model_Location extends ORM {
	
	protected $_belongs_to = [
			'convention' => [],
	];
	
	protected $_has_many = [
			'timeslots' => [ 'model' => 'Timeslot', 'through' => 'timeslot_locations' ],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'convention_id' => [],
			// data fields
			'title' => [],
			'slug' => [],
			'max_attendees' => [],
			'area' => [],
	];
	
	public static function persist(Model_Convention $con, string $title, $area, int $max_attendees = 10, $slug = NULL) : Model_Location {
		$o = new Model_Location();
		$o->convention = $con;
		$o->title = $title;
		$o->area = $area;
		$o->max_attendees = $max_attendees;
		$o->slug = $slug ?: self::gen_slug($title);
		return $o->save();
	}
	
	/**
	 * Retrieve a location by its slug
	 * @param Model_Convention $convention where to lookup the location
	 * @param string $slug slug to look up
	 */
	public static function byConventionSlug(Model_Convention $con, string $slug) : Model_Location {
		$o = (new Model_Location)
			->where('slug', '=', $slug)
			->where('convention_id', '=', $con->pk())
			->find();
		if (!$o->loaded())
			throw new Model_Exception_NotFound();
		return $o;
	}
	
	/**
	 * Check if the location is availale (i.e. not already scheduled) for
	 * the specified duration
	 * @param DateTime $start
	 * @param DateTime $end
	 */
	public function isAvailable(DateTime $start, DateTime $end, &$conflicts = []) {
		foreach ($this->getTimeslots() as $timeslot) {
			if ($timeslot->conflicts($start, $end)) {
				$conflicts[] = $timeslot;
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Retrieve timeslots for this location
	 * @return Database_Result
	 */
	public function getTimeslots() : Database_Result {
		return $this->timeslots
			->where('status','IN',Model_Timeslot::validStatuses())
			->find_all();
	}
	
	/**
	 * Special for_json used by Locations REST API, to prevent infinite recursions
	 * @return array
	 */
	public function for_json_with_timeslots() {
		return array_merge(
				$this->for_json(),
				['timeslots' => self::result_for_json($this->timeslots->where('status','IN',Model_Timeslot::validStatuses())->find_all()) ]
				);
	}
	
	public function for_json() {
		return array_filter(parent::for_json(), function($key){
			return in_array($key, [
					'title', 'area', 'max-attendees', 'slug'
			]);
		},ARRAY_FILTER_USE_KEY);
	}
};
