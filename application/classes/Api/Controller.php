<?php

/**
 * Base class for REST API contorllers
 * @author odeda
 */
abstract class Api_Controller extends Controller {

	public $auto_render = false;
	/**
	 * Request parser
	 * @var Input
	 */
	private $_input = null;
	
	public function __construct($request, $response) {
		parent::__construct($request, $response);
		$this->_input = new Input($request);
	}

	/**
	 * Call from API controllers to verify authorization on calls that require user authorization
	 * @throws HTTP_Exception_403 in case authorization was denied
	 * @return Model_Token The authorization token
	 */
	protected function verifyAuthentication() {
		$auth = $this->request->headers('Authorization') ?: $this->input()->token;
		$auth = $auth ?: Controller_Auth::getSessionLogin(); // if no user submitted auto, try to use auth from session
		Logger::debug("Checking authorization header: " . $auth);
		if (!$auth)
			throw new Api_Exception_Unauthorized($this, "No Authorization header present");
		try {
			$token = Model_Token::byToken($auth);
			if ($token->isExpired())
				throw new Api_Exception_Unauthorized($this, "Authorization token expired");
			return $token;
		} catch (Model_Exception_NotFound $e) {
			Logger::error("Failed to find authorization token '$auth'");
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
		$authen = $this->request->headers('Convention') ?: $this->input()->convention;
		if (!$authen)
			throw new Api_Exception_Unauthorized($this, "No Convention authentication header present");
		Logger::info("Authentication header for convention " . $authen);
		try {
			$apiKey = Model_Api_Key::byClientKey($authen);
			$con = $apiKey->convention;
			Logger::debug("Got convention {$con}");
			@list($type,$auth) = explode(" ",$this->request->headers('Authorization') ?: $this->input()->token);
			if (stristr($type, 'convention')) {
				Logger::debug("Convention tries to authorize");
				@list($time, $salt, $signature) = explode(':', $auth);
				if (abs(time() - (int)$time) > 600) // prevent replay attacks
					throw new Api_Exception_Unauthorized($this, "Invalid convention authorization");
				if (sha1("{$time}:{$salt}".$apiKey->client_secret) != $signature)
					throw new Api_Exception_Unauthorized($this, "Invalid convention authorization ");
				Logger::debug("Convention authorized");
				$con->setAuthorized();
			}
			
			return $con;
		} catch (Model_Exception_NotFound $e) {
			throw new Api_Exception_Unauthorized($this, "Invalid Convention authorization header");
		}
	}
	

	/**
	 * Implement the common behavior of getting a user
	 * from either a user id or a user email (but never both) and
	 * verifying everything
	 * @param int $user_id numeric user ID from the database
	 * @param string $email user's login email
	 */
	protected function loadUserByIdOrEmail($user_id, $email = null) : Model_User {
		if ($user_id and $email)
			throw new Api_Exception_InvalidInput($this, "Please provide either an `id` or `email` but not both");
		if ($user_id) {
			$user = new Model_User($user_id);
			if ($user->loaded())
				return $user;
		}
	
		if (strstr($user_id, '@'))
			$email = $user_id; // sometimes people will pass an email as a user id - you know what: I don't care

		if ($email) {
			try {
				return Model_User::byEmail($email);
			} catch (Model_Exception_NotFound $e) {
				throw new Api_Exception_InvalidInput($this, "Invalid user specified");
			}
		}

		throw new Api_Exception_InvalidInput($this, "Invalid user specified");
	}
	
	/* (non-PHPdoc)
	 * @see Kohana_Controller::execute()
	 */
	public function execute() {
		// handle CORS pre-flight
		if ($this->request->method() == 'OPTIONS') {
			Logger::debug("Answering a pre-flight request to " . $this->request->uri());
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

	protected function generatePreFlightResponse() : Response {
		$this->addCORSHeaders($this->response);
		$this->response->headers('Access-Control-Allow-Methods', 'POST, GET, PUT, DELETE, OPTIONS');
		$this->response->headers('Access-Control-Allow-Headers', 'content-type, authorization, convention, cache-control');
		$this->response->headers('Access-Control-Max-Age', '1728000');
		$this->response->body('');
		Logger::debug("Sending pre-flight response");
		return $this->response;
	}
	
	public static function addQueryToURL($url, $params) {
		$parsed = parse_url($url);
		$query = explode('&',@$parsed['query'] ?: '');
		foreach ($params as $key => $value) {
			$query[] = urlencode($key) . '=' . urlencode($value);
		}
		$parsed['query'] = join('&', $query);
		return self::buildUrl($parsed);
	}
	
	private static function buildUrl($spec) {
		$url = @$spec['scheme'] . "://";
		if (@$spec['user']) {
			$url .= $spec['user'];
			if (@$spec['pass'])
				$url .= ":{$spec['pass']}";
			$url .= "@";
		}
		$url .= @$spec['host'];
		if (@$spec['port'])
			$url .= ":{$spec['port']}";
		$url .= @$spec['path'] ?: '/' ;
		if (@$spec['query'])
			$url .= "?{$spec['query']}";
		if (@$spec['fragment'])
			$url .= "#{$spec['fragment']}";
		return $url;
	}
	
	/**
	 * Return Input object that can be used to query the request data
	 * @return Input Input handling object
	 */
	protected function input() : Input {
		return $this->_input;
	}
	
	/**
	 * Send a response to the caller in JSON format
	 * @param mixed $data Data to send
	 */
	protected function send($data) {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		$this->response->body(json_encode($data, JSON_UNESCAPED_UNICODE));
	}

}
