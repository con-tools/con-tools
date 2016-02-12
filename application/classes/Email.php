<?php

/**
 * Implementation of email sending using a variety of email APIs
 * @author Oded Arbel
 */

class Email {
	
	static $config = null;
	
	public static $default = 'native';
	
	public static function send($from, $to, $subject, $body, $headers = [], $provider = null) {
		self::getImpl($provider ?: self::$default)->send($from, $to, $subject, $body, $headers);
	}
	
	private static function getImpl($provider) {
		self::$config = self::$config ?: static::getConfig();
		if (!$config[$provider])
			throw new Exception("Invalid provider specified: #{$provider}");
		$prov_config = $config[$provider];
		$fullclass = "Email_" . str_replace('/', '_', $prov_config['type']);
		return new $fullclass($prov_config);
	}
	

	private static function getConfig() {
		return Kohana::$config->load('email');
	}
	
}
