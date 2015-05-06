<?php

class Template_Controller extends Controller {
	
	public static $default_template = 'simple';
	
	protected $template = static::$default_template;
	
	protected function before() {
		parent::before();
		$this->template = Twig::factory($this->template);
	}
	
	protected function after() {
		// pre-render the view content, so if it breaks we get a good stack trace
		$content = (string)$this->view;
		$this->view = $this->template;
		$this->view->content = $content;
		
		parent::after(); // call parent to render the view
	}
}
