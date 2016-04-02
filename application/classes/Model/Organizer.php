<?php
class Model_Organizer extends ORM {

	protected $_belongs_to = [
		'convention' => []
	];

	protected $_columns = [
		'id' => [],
		// foreign keys
		'convention_id' => [],
		// data fields
		'title' => []
	];
	
	public static function byTitle($title) : Model_Organizer {
		$o = (new Model_Organizer())->where('title', 'like', $title)->find();
		if ($o->loaded())
			return $o;
		throw new Model_Exception_NotFound();
	}
	
	/**
	 * Return public data for the user - namely, name and email - for JSON presetnation to other people
	 */
	public function for_json() {
		return [
				'id' => $this->pk(),
				'title' => $this->title,
				'convention' => $this->convention->for_json(),
		];
	}
	

}
