<?php

/**
 * Base class for REST API contorllers
 * @author odeda
 */
abstract class Api_Controller extends Controller {

	public $auto_render = false;
	
	public function __construct($request, $response) {
		parent::__construct($request, $response);
	}

	/**
	 * Call from API controllers to verify authorization on calls that require user authorization
	 * @throws HTTP_Exception_403 in case authorization was denied
	 * @return Model_Token The authorization token
	 */
	protected function verifyAuthentication() {
		$auth = $this->request->headers('Authorization') ?: $this->request->query('token');
		if (!$auth)
			throw new Api_Exception_Unauthorized($this, "No Authorization header present");
		try {
			$token = Model_Token::byToken($auth);
			if ($token->is_expired())
				throw new Api_Exception_Unauthorized($this, "Authorization token expired");
			return $token;
		} catch (Model_Exception_NotFound $e) {
			throw new Api_Exception_Unauthorized($this, "Invalid Authorization header");
		}
	}
	
	protected function verifyConventionKey() {
		$auth = $this->request->headers('Convention') ?: $this->request->query('convention');
		if (!$auth)
			throw new Api_Exception_Unauthorized("No Convention authorization header present");
		try {
			$convention = Model_Convention::byAPIKey($auth);
			return $convention;
		} catch (Model_Exception_NotFound $e) {
			throw new Api_Exception_Unauthorized("Invalid Convention authorization header");
		}
	}
	
	/* (non-PHPdoc)
	 * @see Kohana_Controller::execute()
	 */
	public function execute() {
		// handle CORS pre-flight
		if ($this->request->method() == 'OPTIONS') {
			return $this->generatePreFlightResponse();
		}
		
		// otherwise just add the required CORS headers
		$this->addCORSHeaders($this->response);
		return parent::execute();
	}
	
	public function addCORSHeaders($response) {
		$response->headers('Access-Control-Allow-Origin', $this->request->headers('Origin'));
		$response->headers('Access-Control-Allow-Credentials', 'true');
	}

	protected function generatePreFlightResponse() {
		$this->addCORSHeaders($this->response);
		$this->response->headers('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
		$this->response->headers('Access-Control-Allow-Headers', 'content-type, authorization, convention');
		$this->response->headers('Access-Control-Max-Age', '1728000');
		$this->response->body('');
		return $this->response;
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
