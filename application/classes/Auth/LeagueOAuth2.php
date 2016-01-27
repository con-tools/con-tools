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
	
	private $opts = [];
	private $provider_name;

	public function __construct($name, $configuration, $callback_url) {
		$this->client_id = $configuration['id'];
		$this->secret = $configuration['secret'];
		$this->name = $name;
		$this->provider_name = @$configuration['provider'] ?  : 'GenericProvider';
		$this->opts = array_merge([
				'clientId' => $this->client_id,
				'clientSecret' => $this->secret,
				'redirectUri' => $callback_url 
		], @$configuration['config'] ?  : []);
		$this->initProvider();
	}
	
	private function initProvider() {
		$full_provider_name = "League\\OAuth2\\Client\\Provider\\{$this->provider_name}";
		$this->provider = new $full_provider_name($this->opts);
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getAuthenticationURL()
	 */
	public function getAuthenticationURL($redirect_url) {
		$authUrl = $this->provider->getAuthorizationUrl([
				'scope' => [ 'email', 'public_profile' ]
		]);
		Session::instance()->set('oauth2state', $this->provider->getState());
		Session::instance()->set('oauth2-callback-url', $redirect_url);
		return $authUrl;
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::complete()
	 */
	public function complete($params) {
		extract($params); // import $code and $state
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
		return $this->user->getName() ?: explode('@',$this->user->getEmail())[0];
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
	
	/**
	 * We can't serialize the Leageue OAuth object, so clean it before serialization
	 */
	public function __sleep() {
		$this->provider = null;
		return [ 'client_id', 'secret', 'token', 'opts', 'provider_name' ];
	}
	
	/**
	 * recreate the League OAuth provider object
	 */
	public function __wakeup() {
		$this->initProvider();
	}

	/**
	 * {@inheritDoc}
	 * @see Auth_ProviderIf::getNeededQueryParams()
	 */
	public function getNeededQueryParams() {
		return [ 'code', 'state' ];
	}

}