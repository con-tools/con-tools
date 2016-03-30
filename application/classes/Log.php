<?php

class Log extends Kohana_Log {

	public static function debug($message, array $context = NULL, array $additional = NULL) {
		return Kohana::$log->add(self::DEBUG, $message, $context, $additional);
	}
	
	public static function info($message, array $context = NULL, array $additional = NULL) {
		return Kohana::$log->add(self::INFO, $message, $context, $additional);
	}
	
	public static function warn($message, array $context = NULL, array $additional = NULL) {
		return Kohana::$log->add(self::WARNING, $message, $context, $additional);
	}
	
	public static function error($message, array $context = NULL, array $additional = NULL) {
		return Kohana::$log->add(self::ERROR, $message, $context, $additional);
	}
	
	public static function fatal($message, array $context = NULL, array $additional = NULL) {
		return Kohana::$log->add(self::EMERGENCY, $message, $context, $additional);
	}
}