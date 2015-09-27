<?php
class Auth_LeagueOAuth1 implements Auth_ProviderIf {

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
		$this->provider_name = @$configuration['provider'] ? : 'GenericProvider';
		$this->opts = array_merge([
				'identifier' => $this->client_id,
				'secret' => $this->secret,
				'callback_uri' => $callback_url 
		], @$configuration['config'] ?  : []);
		$this->initProvider();
	}
	
	private function initProvider() {
		$full_provider_name = "League\\OAuth1\\Client\\Server\\{$this->provider_name}";
		$this->provider = new $full_provider_name($this->opts);
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getAuthenticationURL()
	 */
	public function getAuthenticationURL($redirect_url) {
		$temp = $this->provider->getTemporaryCredentials();
		$authUrl = $this->provider->getAuthorizationUrl($temp);
		Session::instance()->set('oauth1-temp-creds', $temp);
		Session::instance()->set('oauth1-callback-url', $redirect_url);
		return $authUrl;
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::complete()
	 */
	public function complete($params) {
		extract($params); // import oauth_tokena and oauth_verifier
		if (is_null($oauth_token))
			throw new Auth_Cancelled();
		$temp = Session::instance()->get('oauth1-temp-creds');
		$tokenCredentials = $this->provider->getTokenCredentials($temp, $oauth_token, $oauth_verifier);
		$this->token = $tokenCredentials;
		$this->user = $this->provider->getUserDetails($tokenCredentials);
		error_log("Got OAuth 1 token: " . print_r([
				$this->token->getIdentifier(), 
				$this->token->getSecret(),
				$this->provider->getUserScreenName($this->token),
				$this->provider->getUserEmail($this->token),
				$this->getEmail(),
				$this->getName(),
		],true));
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getName()
	 */
	public function getName() {
		return $this->user->name ?: $this->provider->getUserScreenName($this->token);
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getEmail()
	 */
	public function getEmail() {
		return $this->user->email ?: ($this->provider->getUserEmail($this->token) ?: (
				$this->getProviderName() . '-' . $this->token->getIdentifier() . '@con-troll.org' ));
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
		$this->token->getIdentifier();
	}

	/*
	 * (non-PHPdoc)
	 * @see Auth_ProviderIf::getRedirectURL()
	 */
	public function getRedirectURL() {
		return Session::instance()->get('oauth1-callback-url');
	}
	
	/**
	 * We can't serialize the Leageue OAuth object, so clean it before serialization
	 */
	public function __sleep() {
		$this->provider = null;
		return [ 'client_id', 'secret', 'token', 'opts', 'provider_name', 'name' ];
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
		return [ 'oauth_token', 'oauth_verifier' ];
	}

}