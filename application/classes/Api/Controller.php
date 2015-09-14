<?php

/**
 * Base class for REST API contorllers
 * @author odeda
 */
abstract class Api_Controller extends Controller {

	public $auto_render = false;
	
	public function __construct($request, $response) {
		global $is_api_call;
		$is_api_call = true;
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
	
	protected function verifyConventionKey() {
		$auth = $this->request->headers('Convention') ?: $this->request->query('convention');
		if (!$auth)
			throw new HTTP_Exception_403("No Convention authorization header present");
		try {
			$convention = Model_Convention::byAPIKey($auth);
			return $convention;
		} catch (Model_Exception_NotFound $e) {
			throw new HTTP_Exception_403("Invalid Convention authorization header");
		}
	}
	
	/* (non-PHPdoc)
	 * @see Kohana_Controller::execute()
	 */
	public function execute() {
		// handle CORS pre-flight
		$this->response->headers('Access-Control-Allow-Origin', $this->request->headers('Origin'));
		$this->response->headers('Access-Control-Allow-Credentials', 'true');
		if ($this->request->method() == 'OPTIONS') {
			$this->response->headers('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
			$this->response->headers('Access-Control-Allow-Headers', 'content-type, authorization, convention');
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
