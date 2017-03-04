<?php

class Api_Exception_InvalidInput extends HTTP_Exception_400 {
	
	private $source; // Controller that threw this error
	private $vars;
	
	public function __construct(Api_Controller $source_controller, $message = NULL, $variables = NULL, $previous = NULL) {
		$this->source = $source_controller;
		$this->vars = $variables;
		parent::__construct($message, NULL, $previous);
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
		if ($this->vars)
			$data['details'] = $this->vars;
		$res->headers('Content-Type', 'application/json');
		$res->body(json_encode($data));
		$this->source->addCORSHeaders($res);
		return $res;
	}

}
