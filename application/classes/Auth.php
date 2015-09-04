<?php

class Auth {
	
	public static function getProvider($provider) {
		$config = getConfig();
		if (!$config[$provider])
			throw new Exception("Invalid provider specified: #{$provider}");
		$prov_config = $config[$provider];
		$fullclass = "Auth_" . str_replace('/', '_', $prov_config['type']);
		return new $fullclass($prov_config);
	}
	
	private static function getConfig() {
		return Kohana::$config->load('auth');
	}
}
