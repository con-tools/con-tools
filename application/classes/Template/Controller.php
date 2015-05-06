<?php

class Template_Controller extends Controller {
	
	public static $default_template = 'simple';
	
	protected $template = static::$default_template;
	
	protected function before() {
		parent::before();
		$this->template = Twig::factory($this->template);
	}
	
	protected function after() {
		parent::after();
		
		if ($this->auto_render) {
			// pre-render the view content, so if it breaks we get a good stack trace
			$this->template->content = (string)$this->view; 
			$this->response->body($this->template);
		}
	}
}
