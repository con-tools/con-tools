<?php
	
abstract class Controller extends Kohana_Controller {
	
	public $view;
	public $auto_render = true;
	
	public function before() {
		parent::before();
		
		// initialize default view from class name
		if (empty($this->view)) {
			$viewkey = strtolower(preg_replace('/^Controller_/', '', get_class($this)));
			$this->view = str_replace('_','/',$viewkey);
		}
		
		$this->view = Twig::factory($this->view);
	}
	
	public function after() {
		parent::after();

		if ($this->auto_render) {
			$this->response->body((string)$this->view);
		}
	}
	
	protected function send($data) {
		$this->response->headers('Content-Type', 'application/json');
		$this->response->body(json_encode($data));
		$this->auto_render = false;
	}
}
