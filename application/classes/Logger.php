<?php

class Logger {

	public static function debug($message, array $context = NULL, array $additional = NULL) {
		return Kohana::$log->add(Kohana_Log::DEBUG, $message, $context, $additional);
	}
	
	public static function info($message, array $context = NULL, array $additional = NULL) {
		return Kohana::$log->add(Kohana_Log::INFO, $message, $context, $additional);
	}
	
	public static function warn($message, array $context = NULL, array $additional = NULL) {
		return Kohana::$log->add(Kohana_Log::WARNING, $message, $context, $additional);
	}
	
	public static function error($message, array $context = NULL, array $additional = NULL) {
		return Kohana::$log->add(Kohana_Log::ERROR, $message, $context, $additional);
	}
	
	public static function fatal($message, array $context = NULL, array $additional = NULL) {
		return Kohana::$log->add(Kohana_Log::EMERGENCY, $message, $context, $additional);
	}
}