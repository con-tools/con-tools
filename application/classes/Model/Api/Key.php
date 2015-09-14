<?php

class Model_Api_Key extends ORM {
	
	protected $_belongs_to = [
			'convention' => [],
	];
	
	public static function byClientKey($key) {
		$o = Model::factory('api_key')->where('client_key','=',$key)->find();
		if (!$o->loaded())
			throw new Model_Exception_NotFound();
		return $o;
	}
	
}
