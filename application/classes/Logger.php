<?php

class Logger {
	
	static $instance;
	
	public static function instance() {
		return self::$instance ?: (self::$instance = self::loadLogger());
	}
	
	private static function loadLogger() {
		$prov_config = Kohana::$config->load('logger');
		$fullclass = "Log_" . str_replace('/', '_', ucfirst($prov_config['type']));
		$logger = new $fullclass($prov_config);
		Kohana::$log->attach($logger);
		return $logger;
	}

	public static function debug($message, array $context = NULL, array $additional = NULL) {
		self::instance();
		return Kohana::$log->add(Kohana_Log::DEBUG, $message, self::parseContext($context), $additional);
	}
	
	public static function info($message, array $context = NULL, array $additional = NULL) {
		self::instance();
		return Kohana::$log->add(Kohana_Log::INFO, $message, self::parseContext($context), $additional);
	}
	
	public static function warn($message, array $context = NULL, array $additional = NULL) {
		self::instance();
		return Kohana::$log->add(Kohana_Log::WARNING, $message, self::parseContext($context), $additional);
	}
	
	public static function error($message, array $context = NULL, array $additional = NULL) {
		self::instance();
		return Kohana::$log->add(Kohana_Log::ERROR, $message, self::parseContext($context), $additional);
	}
	
	public static function fatal($message, array $context = NULL, array $additional = NULL) {
		self::instance();
		return Kohana::$log->add(Kohana_Log::EMERGENCY, $message, self::parseContext($context), $additional);
	}
	
	private static function parseContext($context = NULL) {
		if (is_null($context))
			return $context;
		if (is_string($context))
			return [ ':context' => $context ];
		foreach ($context as $name => &$value)
			if (!is_string($value))
				$value = print_r($value, true);
		return $context;
	}
}