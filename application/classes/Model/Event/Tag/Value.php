<?php

class Model_Event_Tag_Value extends ORM {
	
	protected $_belongs_to = [
			'event_tag_type' => [],
	];
	
	protected $_has_many = [
			'event_tags' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'event_tag_type_id' => [],
			// data fields
			'title' => [],
	];
	
	/**
	 * Retrieve a valid tag value if exists, or generate a new one
	 * @param Model_Event_Tag_Type $type event tag type this value belongs to
	 * @param string $title text of the value
	 */
	public static function generate(Model_Event_Tag_Type $type, string $title) {
		$o = $type->event_tag_values->where('title', '=', $title)->find();
		if ($o->loaded())
			return $o;
		return self::persist($type, $title);
	}
	
	public static function persist(Model_Event_Tag_Type $type, string $title) {
		$o = new Model_Event_Tag_Value();
		$o->event_tag_type = $type;
		$o->title = $title;
		$o->save();
		return $o;
	}
	
};
