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
			'requirement' => [], // requirement specification, one of '1' (one and only one), '*' (zero or more) or '+' (one or more)
			'visible' => [ 'type' => 'boolean' ],
	];
	
	/**
	 * Locate the specified event tag type or generate a new one
	 * @param Model_Convention $con Convention to which this type belongs
	 * @param string $title tag type 
	 */
	public static function generate(Model_Convention $con, string $title, $required = true, 
			$support_multiple = false) : Model_Event_Tag_Type {
		$o = $con->event_tag_types->where('title','=', $title)->find();
		if ($o->loaded())
			return $o;
		return self::persist($con, $title, $required, $support_multiple);
	}
	
	public static function persist(Model_Convention $con, string $title, $required = true, 
			$support_multiple = false) : Model_Event_Tag_Type {
		$o = new Model_Event_Tag_Type();
		$o->convention = $con;
		$o->title = $title;
		$o->requirement = $required ? ($support_multiple ? '+' : '1') : '*';
		$o->visible = true;
		$o->save();
		return $o;
	}
	
	public function requiredOne() {
		return $this->requirement == '1';
	}
	
	public function requiredMany() {
		return $this->requirement == '+';
	}
	
	public function optional() {
		return $this->requirement == '*';
	}
	
	public function for_json() {
		return [
				'title' => $this->title,
				'requirement' => $this->requirement,
				'public' => $this->visible ? true : false,
		];
	}
}
