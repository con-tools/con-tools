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
	
	public function redirect_to_action($action) {
		$this->redirect($this->action_url($action));
	}
	
	public function action_url($action, $full = false) {
		$params = $this->request->route()->matches($this->request); // guaranteed a match, otherwise we wouldn't be here
		$params['action'] = $action;
		return URL::site($this->request->route()->uri($params), $full);
	}
	
	protected function send($data) {
		$this->response->headers('Content-Type', 'application/json');
		$this->response->body(json_encode($data));
		$this->auto_render = false;
	}
}
