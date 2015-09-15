<?php
class Auth_LeagueOAuth2 implements Auth_ProviderIf {

	/**
	 * Application's OAuth client ID
	 * 
	 * @var String $client_id
	 */
	private $client_id = null;

	/**
	 * Application's OAuth client secret
	 * 
	 * @var String $secret
	 */
	private $secret = null;

	private $name = null;

	private $provider = null;
	
	/**
	 * retrieved authentication token
	 */
	private $token = null;

	/**
	 * retrieved authenticated user
	 */
	private $user = null;

	public function __construct($name, $configuration, $callback_url) {
		$this->client_id = $configuration['id'];
		$this->secret = $configuration['secret'];
		$this->name = $name;
		$provider_name = $configuration['provider'] ?  : 'GenericProvider';
		$full_provider_name = "League\\OAuth2\\Client\\Provider\\{$provider_name}";
		$opts = array_merge([
				'clientId' => $this->client_id,
				'clientSecret' => $this->secret,
				'redirectUri' => $callback_url 
		], $configuration['config'] ?  : []);
		$this->provider = new $full_provider_name($opts);
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getAuthenticationURL()
	 */
	public function getAuthenticationURL($redirect_url) {
		$authUrl = $this->provider->getAuthorizationUrl([
				'scope' => [ 'email', 'name' ]
		]);
		Session::instance()->set('oauth2state', $this->provider->getState());
		Session::instance()->set('oauth2-callback-url', $redirect_url);
		return $authUrl;
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::complete()
	 */
	public function complete($code, $state) {
		if ($state != Session::instance()->get('oauth2state'))
			throw new Exception("Invalid authorization state!");
		$this->token = $this->provider->getAccessToken('authorization_code', [ 'code' => $code ]);
		$this->user = $this->provider->getResourceOwner($this->token);
		$this->token = $this->provider->getLongLivedAccessToken($this->token);
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getName()
	 */
	public function getName() {
		return $this->user->getName();
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getEmail()
	 */
	public function getEmail() {
		return $this->user->getEmail();
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getProviderName()
	 */
	public function getProviderName() {
		return $this->name;
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getToken()
	 */
	public function getToken() {
		$this->token->getToken();
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getRedirectURL()
	 */
	public function getRedirectURL() {
		return Session::instance()->get('oauth2-callback-url');
	}

}