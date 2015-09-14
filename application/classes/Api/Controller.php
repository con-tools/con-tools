<?php

/**
 * Base class for REST API contorllers
 * @author odeda
 */
abstract class Api_Controller extends Controller {

	public $auto_render = false;

	/**
	 * Call from API controllers to verify authorization on calls that require user authorization
	 * @throws HTTP_Exception_403 in case authorization was denied
	 * @return Model_Token The authorization token
	 */
	protected function verifyAuthentication() {
		$auth = $this->request->headers('Authorization') ?: $this->request->query('token');
		if (!$auth)
			throw new HTTP_Exception_403("No Authorization header present");
		try {
			$token = Model_Token::byToken($auth);
			if ($token->is_expired())
				throw new HTTP_Exception_403("Authorization token expired");
			return $token;
		} catch (Model_Exception_NotFound $e) {
			throw new HTTP_Exception_403("Invalid Authorization header");
		}
	}
	
	/* (non-PHPdoc)
	 * @see Kohana_Controller::execute()
	 */
	public function execute() {
		// handle CORS pre-flight
		if ($this->request->headers('Cookie')) {
			$this->response->headers('Access-Control-Allow-Origin', $this->request->headers('Origin'));
			$this->response->headers('Access-Control-Allow-Credentials', 'true');
		} else {
			$this->response->headers('Access-Control-Allow-Origin', '*');
		}
		if ($this->request->method() == 'OPTIONS') {
			$this->response->headers('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
			$this->response->headers('Access-Control-Allow-Headers', 'content-type, authorization');
			$this->response->headers('Access-Control-Max-Age', '1728000');
			$this->response->body('');
			return $this->response;
		}
		
		return parent::execute();
	}

	
	/**
	 * Send a response to the caller in JSON format
	 * @param mixed $data Data to send
	 */
	protected function send($data) {
		$this->response->headers('Content-Type', 'application/json');
		$this->response->body(json_encode($data));
	}

}
