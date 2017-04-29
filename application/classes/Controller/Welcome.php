<?php

class Controller_Welcome extends Controller {
	
	public function action_index()
	{
		$this->view->name = 'Itai';
	}

	public function action_test() {
		$this->auto_render = false;
		$this->response->body(json_encode((new Model_Api_Key(62))->for_json()));
	}

} // End Welcome
