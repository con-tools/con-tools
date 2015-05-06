<?php

class Controller_Welcome extends Controller {
	public function execute() {
		throw new Exception("test");
	}
	
	public function action_index()
	{
		$this->view->name = 'Itai';
	}

} // End Welcome
