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
	public function __construct($configuration) {
		$this->client_id = $configuration['id'];
		$this->secret = $configuration['secret'];
		$this->openidcon = new OpenIDConnectClient($configuration['endpoint'], $this->client_id, $this->secret);
		$this->openidcon->setRedirectURL();
	}
	
	public function getAuthenticationURL() {
		return $this->openidcon->getAuthenticationURL();
	}
}
