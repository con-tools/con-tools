<?php

class Model_Role extends ORM {
	
	public static $EDIT_EVENTS = 'edit_events';
	
	public static $ROLES = [
			'Model_Role_Administrator',
			'Model_Role_Manager',
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
	
	public static function persiste(Model_Role_Base $role_proto) {
		$o = new Model_Role();
		$o->key = $role_proto->getKey();
		$o->title = $role_proto->getTitle();
		$o->save();
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
	
	public function updateTable() {
		foreach (static::ROLES as $role_class) {
			$role_impl = new $role_class();
			self::persiste($role_impl);
		}
	}
	
};
