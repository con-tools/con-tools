<?php

class Auth {
	
	const USER_AUTH_PROVIDER = 'user-auth-provider';
	
	public static function getProvider($provider, $callback_url) {
		$config = static::getConfig();
		if (!$config[$provider])
			throw new Exception("Invalid provider specified: #{$provider}");
		$prov_config = $config[$provider];
		$fullclass = "Auth_" . str_replace('/', '_', $prov_config['type']);
		$provider = new $fullclass($prov_config, $callback_url);
		Session::instance()->bind(self::USER_AUTH_PROVIDER, $provider);
		return $provider;
	}
	
	public static function getLastProvider() {
		$provider = Session::instance()->get(self::USER_AUTH_PROVIDER);
		if (is_null($provider)) throw new Exception("Failed to find current auth provider");
		return $provider;
	}
	
	private static function getConfig() {
		return Kohana::$config->load('auth');
	}
}
