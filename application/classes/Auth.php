<?php

class Auth {
	
	const USER_AUTH_PROVIDER = 'user-auth-provider';
	
	static private $_last_provider = null;
	
	/**
	 * Initialize and cache a provider implementation for the specified provider name
	 * @return Auth_ProviderIf
	 * @throws Exception
	 */
	public static function getProvider($provider, $callback_url) {
		$config = static::getConfig();
		if (!$config[$provider])
			throw new Exception("Invalid provider specified: #{$provider}");
		$prov_config = $config[$provider];
		$fullclass = "Auth_" . str_replace('/', '_', $prov_config['type']);
		$impl = new $fullclass($provider, $prov_config, $callback_url);
		Session::instance()->bind(self::USER_AUTH_PROVIDER, $impl);
		return $impl;
	}
	
	/**
	 * Find a cached provider implementation
	 * @return Auth_ProviderIf
	 * @throws Exception
	 */
	public static function getLastProvider() {
		static::$_last_provider = static::$_last_provider ?: Session::instance()->get(self::USER_AUTH_PROVIDER);
		if (is_null(static::$_last_provider))
			throw new Exception("Failed to find current auth provider");
		return static::$_last_provider;
	}
	
	private static function getConfig() {
		return Kohana::$config->load('auth');
	}
}
