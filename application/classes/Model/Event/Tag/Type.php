<?php

class Model_Event_Tag_Type extends ORM {
	
	protected $_belongs_to = [
			'convention' => []
	];
	
	protected $_has_many = [
			'event_tag_values' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'convention_id' => [],
			// data fields
			'title' => [],
			'requirement' => [],
			'visible' => [ 'type' => 'boolean' ],
	];
	
	/**
	 * Locate the specified event tag type or generate a new one
	 * @param Model_Convention $con Convention to which this type belongs
	 * @param string $title tag type 
	 */
	public static function generate(Model_Convention $con, string $title) : Model_Event_Tag_Type {
		$o = $con->event_tag_types->where('title','=', $title)->find();
		if ($o->loaded())
			return $o;
		return self::persist($con, $title);
	}
	
	public static function persist(Model_Convention $con, string $title) : Model_Event_Tag_Type {
		$o = new Model_Event_Tag_Type();
		$o->convention = $con;
		$o->title = $title;
		$o->requirement = true;
		$o->visible = true;
		$o->save();
		return $o;
	}
}
