<?php

class Api_Exception_Unimplemented extends HTTP_Exception_400 {
	
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
		$data = [
				'status' => false,
				'error' => $this->getMessage(),
				'server_html' => $res->body()
		];
		$res->headers('Content-Type', 'application/json');
		$res->body(json_encode($data));
		$this->source->addCORSHeaders($res);
		return $res;
	}
	
}
