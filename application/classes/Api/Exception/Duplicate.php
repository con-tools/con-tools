<?php

class Api_Exception_Duplicate extends HTTP_Exception_409 {
	private $source; // Controller that thrown this error
	
	public function __construct(Api_Controller $source_controller = null, $message = NULL, $variables = NULL, $previous = NULL) {
		$this->source = $source_controller;
		parent::__construct($message, $variables, $previous);
	}
	
	public function setControll(Api_Controller $controller) {
		if (!$this->source)
			$this->source = $controller;
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
				//'server_html' => $res->body()
		];
		$res->headers('Content-Type', 'application/json');
		$res->body(json_encode($data));
		if ($this->source)
			$this->source->addCORSHeaders($res);
		return $res;
	}
	
}
