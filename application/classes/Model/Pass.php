<?php

class Model_Pass extends ORM {
	
	const STATUS_ACTIVE = 0;
	const STATUS_CANCELLED = 1;
	
	protected $_has_many = [
			'pass_requirements' => [
					'model' => 'Pass_Requirement',
					'through' => 'pass_requirements_passes'
			],
	];
	
	protected $_belongs_to = [
			'convention' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'convention_id' => [],
			// data fields
			'slug' => [],
			'title' => [],
			'public' => [], // whether this pass should be shown in public listing
			'status' => [], // whether this pass is active or not
			'price' => [], // cost of this pass
	];
	
	/**
	 * Create a new pass and store it in the database
	 * @param Model_Convention $convention owner
	 * @param string $title name of the pass (used in manager UI)
	 * @param boolean $public whether to make this pass available to the public
	 * @param string $price cost of the pass (put in a string please)
	 * @return Model_Pass pass record created
	 */
	public static function persist(Model_Convention $convention, $title, $public, $price) : Model_Pass {
		$o = new Model_Pass();
		$o->convention = $convention;
		$o->slug = self::gen_slug($title);
		$o->title = $title;
		$o->public = $public;
		$o->price = $price;
		$o->status = self::STATUS_ACTIVE;
		$o->save();
		return $o;
	}
	
	public function cancel() {
		$this->status = self::STATUS_CANCELLED;
		$this->save();
	}
	
	public function for_json() {
		return array_merge(parent::for_json(), [
				'pass_requirements' => self::result_for_json($this->pass_requirements->find_all()),
		]);
	}
}