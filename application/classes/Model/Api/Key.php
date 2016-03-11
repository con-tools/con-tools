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
	
	public static function persist(Model_Convention $convention) {
		$obj = new Model_Api_Key();
		$obj->convention = $convention;
		$obj->client_key = self::debase64(base64_encode(random_bytes(20) . sha1($convention->title . random_bytes(20) . time())));
		$obj->client_secret = self::debase64(base64_encode(random_bytes(10))) . '_' . dechex(time());
		$obj->save();
		return $obj;
	}
	
	private static function debase64($string) {
		return str_replace('/','-',str_replace('+','-',str_replace('=','',$string)));
	}
	
}
