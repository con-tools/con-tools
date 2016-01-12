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
			if ($token->isExpired())
				throw new Api_Exception_Unauthorized($this, "Authorization token expired");
			return $token;
		} catch (Model_Exception_NotFound $e) {
			throw new Api_Exception_Unauthorized($this, "Invalid Authorization header");
		}
	}
	
	/**
	 * Check that the conention authentication and possibly authorization is legal and retrieve
	 * the convention record
	 * @return Model_Convention
	 * @throws Api_Exception_Unauthorized
	 */
	protected function verifyConventionKey() {
		$authen = $this->request->headers('Convention') ?: $this->request->query('convention');
		if (!$authen)
			throw new Api_Exception_Unauthorized($this, "No Convention authentication header present");
		try {
			$apiKey = Model_Api_Key::byClientKey($authen);
			$con = $apiKey->convention;
			@list($type,$auth) = explode(" ",$this->request->headers('Authorization') ?: $this->request->query('token'));
			if (stristr($type, 'convention')) {
				@list($time, $salt, $signature) = explode(':', $auth);
				if (abs(time() - (int)$time) > 600) // prevent replay attacks
					throw new Api_Exception_Unauthorized($this, "Invalid convention authorization");
				if (sha1("{$time}:{$salt}".$apiKey->client_secret) != $signature)
					throw new Api_Exception_Unauthorized($this, "Invalid convention authorization ");
				$con->setAuthorized();
			}
			
			return $con;
		} catch (Model_Exception_NotFound $e) {
			throw new Api_Exception_Unauthorized($this, "Invalid Convention authorization header");
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
