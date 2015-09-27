<?php

class Auth_OpenIDConnect implements Auth_ProviderIf {
	
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
	
	private $name = null;
	
	/**
	 * OpenID connect library
	 * @var OpenIDConnectClient $openidcon
	 */
	private $openidcon;
	
	/**
	 * Create an Open ID Connect handler for Con-Troll authentication
	 * @param array $configuration Named array that must include 'id', 'secret' and 'endpoint
	 */
	public function __construct($name, $configuration, $callback_url) {
		$this->client_id = $configuration['id'];
		$this->secret = $configuration['secret'];
		$this->name = $name;
		$this->openidcon = new OpenIDConnectClient($configuration['endpoint'], $this->client_id, $this->secret, new Auth_OpenIDConnection_SessionStorage());
		error_log("Setting OpenID Connect callback URL to $callback_url");
		$this->openidcon->setRedirectURL($callback_url);
		$this->openidcon->addScope('email', 'name');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getAuthenticationURL()
	 */
	public function getAuthenticationURL($redirect_url) {
		Session::instance()->set('openid-connect-auth-callback-url', $redirect_url);
		return $this->openidcon->getAuthenticationURL();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::complete()
	 */
	public function complete($params) {
		extract($params); // import $code and $state
		$this->openidcon->completeAuthorization($code, $state);
		return [
				'email' => $this->getEmail(),
				'name' => $this->getName(),
				];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getName()
	 */
	public function getName() {
		return $this->openidcon->requestUserInfo('name');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getEmail()
	 */
	public function getEmail() {
		return $this->openidcon->requestUserInfo('email');
	}
	/* (non-PHPdoc)
	 * @see Auth_ProviderIf::getProviderName()
	 */
	public function getProviderName() {
		return $this->name;
	}

	/* (non-PHPdoc)
	 * @see Auth_ProviderIf::getToken()
	 */
	public function getToken() {
		return $this->openidcon->getAccessToken();
	}

	/* (non-PHPdoc)
	 * @see Auth_ProviderIf::getRedirectURL()
	 */
	public function getRedirectURL() {
		return Session::instance()->get('openid-connect-auth-callback-url');
	}

	/**
	 * {@inheritDoc}
	 * @see Auth_ProviderIf::getNeededQueryParams()
	 */
	public function getNeededQueryParams() {
		return [ 'code', 'state' ];
	}

}
