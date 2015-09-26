<?php

class Api_Exception_Unauthorized extends HTTP_Exception_403 {
	
	private $source; // Controller that thrown this error
	
	public function __construct(Api_Controller $source_controller, $message = NULL, $variables = NULL, $previous = NULL) {
		$this->source = $source_controller;
		parent::__construct($message, $variables, $previous);
	}
	
	/**
	 * {@inheritDoc}
	 * @see Kohana_HTTP_Exception::get_response()
	 */
	public function get_response()
	{
		$res = Kohana_Exception::response($this);
		$this->source->addCORSHeaders($res);
		return $res;
	}
	
}