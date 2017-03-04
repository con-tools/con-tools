<?php

class Model_Pass_Requirement extends ORM {
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'convention_id' => [],
			// data fields
			'slug' => [],
			'title' => [],
			'start_time' => [ 'type' => 'DateInterval' ], // for automatic time-based association, default NULL
			'end_time' => [ 'type' => 'DateInterval' ], // for automatic time-based association, default NULL
	];
	
	protected $_belongs_to = [
			'convention' => [],
	];
	
	/**
	 * Create a new pass requirement and store it in the database
	 * @param Model_Convention $convention owner
	 * @param string $title name of pass requirement (used in manager UI)
	 */
	public static function persist(Model_Convention $convention, $title) {
		$o = new Model_Pass_Requirement();
		$o->convention = $convention;
		$o->slug = $this->gen_slug($title);
		$o->title = $title;
		$o->save();
		return $o;
	}
	
}
