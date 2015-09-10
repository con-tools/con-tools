<?php
class Model_User extends ORM {

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
	
	public static function persist($name, $email, $provider) {
		$o = new Model_User();
		$o->name = $name;
		$o->email = $email;
		$o->provider = $provider;
		$o->login_time = new DateTime();
		$o->save();
		return $o;
	}

}
