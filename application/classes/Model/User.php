<?php

class Model_User extends ORM {
	
	const NOT_REALLY_EMAIL = 'invalid@con-troll.org'; // hack to store "I need this user to input her email" status
	
	const PASSWORD_HASH_OPTIONS = [
			'cost' => 17
	];
	
	const PASSWORD_PROVIDER = 'password';
	
	protected $_columns = [
			'id' => [],
			// data fields
			'name' => [],
			'email' => [],
			'phone' => [],
			'date_of_birth' => [ 'type' => 'DateTime' ],
			'provider' => [], // identity provider
			'password' => [], // may not actually be a password, but if it is, its crypted
			'created_time' => [ 'type' => 'DateTime' ],
			'login_time' => [ 'type' => 'DateTime' ],
	];
	
	protected $_has_many = [
			'tokens' => [],
			'organizers' => [],
			'events' => [],
			'contact_for' => [ 'model' => 'event', 'foreign_key' => 'staff_contact_id' ],
			'timeslots' => [ 'model' => 'timeslot', 'through' => 'timeslot_hosts' ],
			'issues' => [ 'model' => 'crm_issues', 'foreign_key' => 'agent_id' ],
			'messages' => [ 'model' => 'crm_messages', 'foreign_key' => 'sender_id' ],
			'tickets' => [],
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
		$this->save();
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
			if (!$token->isExpired())
				return $token;
		}
		
		return Model_Token::persist($this, $type, Time_Unit::weeks(2));
	}
	
	public static function persist($name, $email, $provider, $token) {
		if ($email == '-')
			$email = self::NOT_REALLY_EMAIL;
		
		// try to figure out if this user already has an account, first by token:
		try {
			return static::byProviderToken($provider, $token);
		} catch (Model_Exception_NotFound $e) {}; // haven't found a user to update
		
		// some providers switch tokens, so we'll trust the provided email address
		if ($provider != self::PASSWORD_PROVIDER) // unless the user provided the email, whom I can't trust
			try {
				$user = static::byEmail($email);
				// update fields
				if ($name) 
					$user->name = $name;
				$user->provider = $provider;
				$user->password = $token;
				$user->save();
				return $user; 
			} catch (Model_Exception_NotFound $e) {}
			
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
	
	public static function persistWithPassword($name, $email, $password) {
		return static::persist($name, $email, self::PASSWORD_PROVIDER, 
				password_hash($password, PASSWORD_DEFAULT, self::PASSWORD_HASH_OPTIONS));
	}
	
	public static function byProviderToken($provider, $token) {
		if ($provider == self::PASSWORD_PROVIDER)
			throw new Model_Exception_NotFound(); // no looking up users by their passwords
		$o = Model::factory("user")
			->where('provider','=',$provider)
			->where('password', '=', "$token")
			->find();
		if (!$o->loaded())
			throw new Model_Exception_NotFound();
		error_log("Looking up user for $provider:$token, found " . $o->id);
		return $o;
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
