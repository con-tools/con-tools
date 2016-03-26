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
	 * @param string $slug slug to look up
	 */
	public static function bySlug(string $slug) : Model_Location {
		$o = (new Model_Location)->where('slug', '=', $slug)->find();
		if (!$o->loaded())
			throw new Model_Exception_NotFound();
		return $o;
	}
	
	/**
	 * Retrieve timeslots for this location
	 * @return Database_Result
	 */
	public function getTimeslots() : Database_Result {
		return $this->timeslots->find_all();
	}
	
	public function for_json() {
		$ar = array_filter(parent::for_json(), function($key){
			return in_array($key, [
					'title', 'area', 'max-attendees', 'slug'
			]);
		},ARRAY_FILTER_USE_KEY);
		$ar['timeslots'] = ORM::result_for_json($this->timeslots->find_all());
		return $ar;
	}
};
