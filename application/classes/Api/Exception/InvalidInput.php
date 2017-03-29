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
		$res->body($this->smart_json_encode($data));
		$this->source->addCORSHeaders($res);
		return $res;
	}
	
	private function smart_json_encode($data) {
		if (is_array($data)) {
			return '[' . join(',', array_map(function ($obj) {
				return $this->smart_json_encode($obj);
			}, $data)) . ']';
		} elseif ($data instanceof ORM) {
			return json_encode($data->for_json());
		} else
			return json_encode($data);
	}

}
