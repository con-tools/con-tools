<?php

class Model_User extends ORM {
	
	const NOT_REALLY_EMAIL = 'invalid@con-troll.org'; // hack to store "I need this user to input her email" status
	
	const PASSWORD_HASH_OPTIONS = [
			'cost' => 17
	];
	
	protected $_columns = [
			'created_time' => [ 'type' => 'DateTime' ],
			'login_time' => [ 'type' => 'DateTime' ],
	];
	
	protected $_has_many = [
			'tokens' => [],
	];
	
	public function rules() {
		return [
				'email' => [ 
						[ 'not_empty' ], 
						[ 'email' ],
				],
				'name' => [
						[ 'not_empty' ]
				],
		];
	}
	
	/**
	 * Perform a login by retrieving or generating a relevant login token
	 * and updating all relevant time fields 
	 * @param string $type type of token to use. One of 'web', 'api'
	 */
	public function login($type = 'web') {
		$token = $this->getValidToken($type);
		$token->touch();
		$this->login_time = new DateTime();
		return $token;
	}
	
	public function emailIsValid() {
		return $this->email != '-';
	}
	
	public function get($column) {
		if ($column == 'email' && parent::get($column) == self::NOT_REALLY_EMAIL)
			return '-';
		return parent::get($column);
	}
	
	private function getValidToken($type) {
		foreach ($this->tokens->where('type', '=', $type)->find_all() as $token) {
			if (!$token->is_expired())
				return $token;
		}
		
		return Model_Token::persist($this, $type, Time_Unit::weeks(2));
	}
	
	public static function persist($name, $email, $provider, $token) {
		if ($email == '-')
			$email = self::NOT_REALLY_EMAIL;
		try {
			$user = static::byEmail($email);
			// update fields
			if ($name) 
				$user->name = $name;
			$user->provider = $provider;
			$user->password = $token;
			$user->save();
			return $user; 
		} catch (Model_Exception_NotFound $e) {
			// need to create a new account
			$o = new Model_User();
			$o->name = $name;
			$o->email = $email;
			$o->provider = $provider;
			$o->password = $token;
			$o->login_time = new DateTime();
			$o->save();
			return $o;
		}
	}
	
	public static function persistWithPassword($name, $email, $password) {
		return static::persist($name, $email, 'password', 
				password_hash($password, PASSWORD_DEFAULT, self::PASSWORD_HASH_OPTIONS));
	}
	
	public static function byEmail($email) {
		$o = Model::factory("user")->where('email','=',$email)->find();
		if (!$o->loaded())
			throw new Model_Exception_NotFound();
		return $o;
	}
	
	public static function byPassword($email, $password) {
		$user = Model_User::byEmail($email);
		if (!password_verify($password, $user->password))
			throw new Model_Exception_NotFound(); // invalid password
			
		// Check if a newer hashing algorithm is available or the cost has changed
		if (password_needs_rehash($user->password, PASSWORD_DEFAULT, self::PASSWORD_HASH_OPTIONS)) {
			$user->password = password_hash($password, PASSWORD_DEFAULT, self::PASSWORD_HASH_OPTIONS);
			$user->save();
		}
		return $user;
	}
}
