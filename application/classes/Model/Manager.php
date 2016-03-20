<?php

/**
 * Convention managers
 * 
 * May not actually list convention managers - just people who have some kind
 * of management capabilities on the convention. Currently for simplicity we
 * have only one type of role here: convention manager, which is a full administrator
 * 
 * @author odeda
 *
 */
class Model_Manager extends ORM {
	
	protected $_belongs_to = [
			'convention' => [],
			'user' => [],
			'role' => [],
	];
	
	protected $_columsn = [
			'id' => [],
			// foreign keys
			'convention_id' => [],
			'user_id' => [],
			'role_id' => [],
	];
	
	public static function persist(Model_Convention $con, Model_User $user, Model_Role $role) {
		$o = new Model_Manager();
		$o->convention = $con;
		$o->user = $user;
		$o->role = role;
		$o->save();
		return $o;
	}
}
