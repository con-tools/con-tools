<?php
abstract class Api_Controller extends Controller {

	public $auto_render = false;

	protected function send($data) {
		$this->response->headers('Content-Type', 'application/json');
		$this->response->body(json_encode($data));
	}

}
