<?php
class Model_User_Record extends ORM {
	
	protected $_columns = [
			'created_time' => [ 'type' => 'DateTime' ],
	];
	
	
	protected $_belongs_to = [
			'user' => [],
			'convention' => [],
	];
	
	public function isPublicReadable() {
		return in_array($this->acl, [ 'public', 'public-read' ]);
	}
	
	public static function persist(Model_Convention $con, Model_User $user, $descriptor, $content_type, $data) {
		$o = new Model_User_Record();
		$o->convention = $con;
		$o->user = $user;
		$o->descriptor = $descriptor;
		$o->content_type = $content_type;
		$o->data = $data;
		$o->save();
		return $o;
	}

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
}
