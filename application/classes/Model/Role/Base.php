<?php

class Model_Role_Base {
	
	protected $role;
	protected $_title = '';
	protected $_privileges = [];
	
	public function __construct(Model_Role $role_data) {
		$this->role = $role_data;
	}
	
	public function check_privilege($priv) {
		return $this->role->$priv ? true : false;
	}

	public function get_title() {
		return $this->_title;
	}
	
	public function get_privileges() {
		return $this->_privileges;
	}
}