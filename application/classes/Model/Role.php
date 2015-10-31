<?php

class Model_Role extends ORM {
	
	public static $EDIT_EVENTS = 'edit_events';
	
	public static $ROLES = [
			'Model_Role_Administrator',
			'Model_Role_Manager',
	];
	
	protected $_has_many = [
			'organizers' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// data fields
			'key' => [],
			'title' => [],
	];
	
	private $impl = null;
	
	public function has_privilege($priv) {
		return getImpl()->check_privilege($priv);
	} 
	
	private function getImpl() {
		if ($this->impl)
			return $this->impl;
		$clazz = 'Model_Role_' . $this->key;
		return $this->impl = new $clazz();
	}
	
	public function update_table() {
		foreach (static::ROLES as $role_class) {
			$role_impl = new $role_class();
			// TODO: actually implement the generator
		}
	}
	
};
