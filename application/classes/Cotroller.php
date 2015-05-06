<?php
	
abstract class Controller extends Kohana_Controller {
	
	public $view;
	public $auto_render = true;
	
	protected function before() {
		parent::before();
		
		// initialize default view from class name
		if (empty($this->view)) {
			$viewkey = strtolower(preg_replace('/^Controller_/', '', get_class($this)));
			$this->view = str_replace('_','/',$viewkey);
		}
		
		$this->view = Twig::factory($this->view);
	}
	
}
