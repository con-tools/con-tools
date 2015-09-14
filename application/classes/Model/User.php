<?php

class Model_User extends ORM {
	
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
						[ 'regex', [ ':value', '/^[^@]+@([\w_-]+\.)+\w{2,4}/' ] ]
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
	
	private function getValidToken($type) {
		foreach ($this->tokens->where('type', '=', $type)->find_all() as $token) {
			if (!$token->is_expired())
				return $token;
		}
		
		return Model_Token::persist($this, $type, Time_Unit::weeks(2));
	}
	
	public static function persist($name, $email, $provider, $token) {
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
	
	public static function byEmail($email) {
		$o = Model::factory("user")->where('email','=',$email)->find();
		var_dump($o);
		if (!$o->loaded())
			throw new Model_Exception_NotFound();
		return $o;
	}
	
}
