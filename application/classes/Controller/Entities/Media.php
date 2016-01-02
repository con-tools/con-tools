<?php

class Controller_Entities_Media extends Api_Rest_Controller {
	
	function create($con, $user, $data) {
		
	}
	
	function retrieve($con, $user, $id) {
		return (object)((new Model_Medium($id))->as_array());
	}
	
	function update($con, $user, $id, $data) {
	}
	
	function delete($con, $user, $id) {
		(new Model_Medium($id))->delete();
		return true;
	}
	
}
