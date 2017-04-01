<?php

class Model_Role extends ORM {
	
	public const CAPABILITY_EDIT_EVENTS = 'edit_events';
	public const CAPABILITY_LIST_EVENTS = 'list_events';
	public const CAPABILITY_CREATE_USER = 'create_user';
	public const CAPABILITY_CREATE_USER_PASS = 'create_user_pass';
	public const CAPABILITY_CREATE_TICKET = 'create_ticket';
	public const CAPABILITY_CREATE_SALE = 'create_sale';
	
	public const ROLES = [
			'Model_Role_Administrator',
			'Model_Role_Manager',
			'Model_Role_Cashier',
	];
	
	protected $_has_many = [
			'managers' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// data fields
			'key' => [],
			'title' => [],
	];
	
	private $impl = null;
	
	/**
	 * Generate a new role record
	 * @param Model_Role_Base $role_proto Role implementation
	 * @return Model_Role role generated
	 */
	public static function persist(Model_Role_Base $role_proto) : Model_Role  {
		$o = new Model_Role();
		$o->key = $role_proto->getKey();
		$o->title = $role_proto->getTitle();
		$o->save();
		return $o;
	}
	
	/**
	 * Load a role by its key
	 * @param string $key
	 * @return Model_Role role loaded
	 */
	public static function byKey(string $key) : Model_Role {
		$o = (new Model_Role)->where('key', '=', $key)->find();
		if (!$o->loaded())
			throw new Model_Exception_NotFound();
		return $o;
	}
	
	public function hasPrivilege($priv) {
		return getImpl()->check_privilege($priv);
	}
	
	private function getImpl() {
		if ($this->impl)
			return $this->impl;
		$clazz = 'Model_Role_' . ucfirst($this->key);
		return $this->impl = new $clazz();
	}
	
	public static function updateTable() {
		foreach (static::ROLES as $role_class) {
			$role_impl = new $role_class();
			static::persist($role_impl);
		}
	}
	
};
