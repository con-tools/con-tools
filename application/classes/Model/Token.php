<?php

class Model_Token extends ORM {
	
	protected $_columns = [
			'created_time' => [ 'type' => 'DateTime' ],
			'access_time' => [ 'type' => 'DateTime' ],
	];
	
	protected $_belongs_to = [
			'user' => [],
	];
	
	/**
	 * Check if the token has expired. Expired tokens are immediately deleted
	 * @return boolean whether the token has expired and was deleted
	 */
	public function is_expired() {
		if ($this->created_time->getTimestamp() + $this->expiry >= time())
			return false;
		$this->delete();
		return true;
	}
	
	/**
	 * Update last access time
	 */
	public function touch() {
		$this->access_time = new DateTime();
	}

	/**
	 * Create a new token of the specified type and store it in the database
	 * @param Model_User $user owner
	 * @param string $type token type to create
	 * @param integer $expire number of seconds until the token expires
	 */
	public static function persist(Model_User $user, $type, $expire) {
		$o = new Model_Token();
		$o->user = $user;
		$o->type = $type;
		$o->expiry = $expire;
		$o->token = self::genToken($user->id);
		$o->save();
		return $o;
	}
	
	private static function genToken($iv) {
		if (is_numeric($iv)) $iv = $iv << mt_rand(0,10);
		return rtrim(base64_encode(sha1(mt_rand() . $iv . time(), true)),'=');
	}
}
