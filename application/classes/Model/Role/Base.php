<?php

class Model_Role_Base {
	
	protected $role;
	protected $_title = '';
	protected $_privileges = [];
	
	public function __construct(Model_Role $role_data = NULL) {
		if (is_null($role_data)) {
			// generate or load role
			try {
				$role_data = Model_Role::byKey($this->getKey());
			} catch (Model_Exception_NotFound $e) {
				// need to create it
				$role_data = Model_Role::persist($this);
			}
		}
		$this->role = $role_data;
	}
	
	public function getRole() {
		return $this->role;
	}
	
	public function getKey() {
		return strtolower(array_slice(explode('_',get_class($this)),-1)[0]);
	}
	
	public function checkPrivilege($priv) {
		return $this->role->$priv ? true : false;
	}

	public function getTitle() {
		return $this->_title;
	}
	
	public function getPrivileges() {
		return $this->_privileges;
	}
}