<?php
class Model_User_Record extends ORM {
	
	protected $_columns = [
			'created_time' => [ 'type' => 'DateTime' ],
	];
	
	
	protected $_belongs_to = [
			'user' => [],
			'convention' => [],
	];

	public static function byDescriptor(Model_Convention $con, Model_User $user, $descriptor) {
		Model::factory('user_record')
			->where('convention_id', '=', $con->primary_key())
			->where('user_id', '=', $user->primary_key())
			->where('descriptor', '=', $descriptor);
	}
}
