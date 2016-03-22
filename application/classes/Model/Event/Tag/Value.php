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
	public static function generate(Model_Event_Tag_Type $type, string $title) : Model_Event_Tag_Value {
		try {
			return self::byTitle($type, $title);
		} catch (Model_Exception_NotFound $e) {
			return self::persist($type, $title);
		}
	}
	
	public static function persist(Model_Event_Tag_Type $type, string $title) : Model_Event_Tag_Value {
		$o = new Model_Event_Tag_Value();
		$o->event_tag_type = $type;
		$o->title = $title;
		$o->save();
		return $o;
	}
	
	public static function byTitle(Model_Event_Tag_Type $type, string $title) : Model_Event_Tag_Value {
		$o = $type->event_tag_values->where('title', '=', $title)->find();
		if ($o->loaded())
			return $o;
		throw new Model_Exception_NotFound();
	}
	
	/**
	 * Typed helper for lazy folks
	 * @return Model_Event_Tag_Type type who owns this value
	 */
	public function getType() : Model_Event_Tag_Type {
		return $this->event_tag_type;
	}
	
	public function get($column) {
		switch ($column) {
			case 'type':
				return $this->event_tag_type->title;
			default:
				return parent::get($column);
		}
	}
	
};
