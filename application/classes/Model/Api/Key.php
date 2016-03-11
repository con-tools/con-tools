<?php

class Model_Api_Key extends ORM {
	
	protected $_belongs_to = [
			'convention' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'convention_id' => [],
			// data fields
			'client_key' => [],
			'client_secret' => [],
	];
	
	/**
	 * Find the API key
	 * @param string $key Client API key
	 * @return Model_Api_Key
	 * @throws Model_Exception_NotFound
	 */
	public static function byClientKey($key) {
		$o = Model::factory('api_key')->where('client_key','=',$key)->find();
		if (!$o->loaded())
			throw new Model_Exception_NotFound();
		return $o;
	}
	
}
