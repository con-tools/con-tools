<?php

class Auth_OpenIDConnect {
	
	/**
	 * Application's OAuth client ID
	 * @var String $client_id
	 */
	private $client_id = null;
	
	/**
	 * Application's OAuth client secret
	 * @var String $secret
	 */
	private $secret = null;
	
	/**
	 * OpenID connect library
	 * @var OpenIDConnectClient $openidcon
	 */
	private $openidcon;
	
	/**
	 * Create an Open ID Connect handler for Con-Troll authentication
	 * @param array $configuration Named array that must include 'id', 'secret' and 'endpoint
	 */
	public function __construct($configuration, $callback_url) {
		$this->client_id = $configuration['id'];
		$this->secret = $configuration['secret'];
		$this->openidcon = new OpenIDConnectClient($configuration['endpoint'], $this->client_id, $this->secret);
		error_log("Setting OpenID Connect callback URL to $callback_url");
		$this->openidcon->setRedirectURL($callback_url);
		$this->openidcon->addScope('email', 'name');
	}
	
	public function complete($code, $state) {
		$this->openidcon->completeAuthorization($code, $state);
		return $this->openidcon->getAccessToken();
	}
	
	public function getAuthenticationURL() {
		return [
				$this->openidcon->getAuthenticationURL(),
				$this->openidcon->requestUserInfo('email'),
				$this->openidcon->requestUserInfo('name'),
				];
	}
}
