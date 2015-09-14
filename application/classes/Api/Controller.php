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
	
	/**
	 * Send a response to the caller in JSON format
	 * @param mixed $data Data to send
	 */
	protected function send($data) {
		$this->response->headers('Content-Type', 'application/json');
		$this->response->body(json_encode($data));
	}

}
