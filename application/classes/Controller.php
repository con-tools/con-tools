<?php
abstract class Controller extends Kohana_Controller {

	public $view;

	public $auto_render = true;

	public function before() {
		parent::before();
		
		// initialize default view from class name
		if (empty($this->view)) {
			$viewkey = strtolower(preg_replace('/^Controller_/', '', get_class($this)));
			$this->view = str_replace('_', '/', $viewkey);
		}
		
		$this->view = Twig::factory($this->view);
	}

	public function after() {
		parent::after();
		
		if ($this->auto_render) {
			$this->response->body(( string ) $this->view);
		}
	}

	public function redirect_to_action($action, $query = null) {
		$url = $this->action_url($action);
		if (is_string($query))
			$url .= '?' . $query;
		elseif (is_array($query)) {
			$q = [];
			foreach ($query as $key => $val)
				$q[] = urlencode($key) . '=' . urlencode($val).
			$url .= '?' . join('&',$q);
		}
		throw new Exception("Checking my arguments: " . $url);
		$this->redirect($url);
	}

	public function action_url($action, $full = false) {
		$params = $this->request->route()->matches($this->request); // guaranteed a match, otherwise we wouldn't be here
		$params ['action'] = $action;
		unset($params['id']);
		return URL::site($this->request->route()->uri($params), $full);
	}

}
