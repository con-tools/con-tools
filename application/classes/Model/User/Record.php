<?php
class Model_User_Record extends ORM {
	
	protected $_columns = [
			'created_time' => [ 'type' => 'DateTime' ],
	];
	
	
	protected $_belongs_to = [
			'user' => [],
			'convention' => [],
	];
	
	/**
	 * Check if a record is publicly readable
	 * @return boolean public read status
	 */
	public function isPublicReadable() {
		return in_array($this->acl, [ 'public', 'public-read' ]);
	}
	
	/**
	 * Store a new user record
	 * @param Model_Convention $con Convention this record belongs to
	 * @param Model_User $user User this record belongs to
	 * @param unknown $descriptor user/convention unique identifier for the record
	 * @param unknown $content_type type of encoding in the data
	 * @param unknown $data data to store
	 * @return Model_User_Record record created
	 */
	public static function persist(Model_Convention $con, Model_User $user, $descriptor, $content_type, $data, $acl = 'private') {
		$o = new Model_User_Record();
		$o->convention = $con;
		$o->user = $user;
		$o->descriptor = $descriptor;
		$o->content_type = $content_type;
		$o->acl = static::isValidACL($acl) ? $acl : 'private';
		$o->data = $data;
		$o->save();
		return $o;
	}

	/**
	 * Retrieve all user records from the database
	 * @param Model_Convention $con Convention this record belongs to
	 * @param string $descriptor user/convention unique identifier for the record
	 * @throws Model_Exception_NotFound in case there is no such records
	 * @return Model_User_Record record found
	 */
	public static function allByDescriptor(Model_Convention $con, $descriptor, $get_all_versions = FALSE) {
		$o = Model::factory('user_record')
			->where('convention_id', '=', $con->id)
			->where('descriptor', '=', $descriptor)
			->with('user')
			->order_by('created_time','DESC');
		$result = [];
		$userids = [];
		error_log("Fetch all records: {$get_all_versions}");
		foreach ($o->find_all() as $record) {
			if (array_key_exists($record->user_id, $userids) and !$get_all_versions)
				continue;
			$userids[$record->user_id] = true;
			$result[] = array_merge($record->as_array(), [
					'user' => $record->user->export(),
			]);
		}
		return $result;
	}

	/**
	 * Retrieve a user record from the database
	 * @param Model_Convention $con Convention this record belongs to
	 * @param Model_User $user User this record belongs to
	 * @param string $descriptor user/convention unique identifier for the record
	 * @throws Model_Exception_NotFound in case there is no such records
	 * @return Model_User_Record record found
	 */
	public static function byDescriptor(Model_Convention $con, Model_User $user, $descriptor) {
		$o = Model::factory('user_record')
			->where('convention_id', '=', $con->id)
			->where('user_id', '=', $user->id)
			->where('descriptor', '=', $descriptor)
			->order_by('created_time','DESC')
			->find();
		if (!$o->loaded())
			throw new Model_Exception_NotFound();
		return $o;
	}
	
	public static function isValidACL($acl) {
		return in_array($acl, [
				'private',
				'public-read',
				'public',
		]);
	}
}
