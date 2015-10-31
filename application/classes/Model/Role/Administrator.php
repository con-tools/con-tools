<?php

class Model_Role_Administrator extends Model_Role_Base {
	
	protected $_title = 'System Administrator';
	protected $_privileges = []; // no need to enumerate privileges because check_privilege always autuhorizes
	
	public function check_privilege($priv) {
		return true;
	}
};
