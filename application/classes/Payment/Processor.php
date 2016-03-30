<?php

class Payment_Processor {
	
	private static $instances = [];
	
	public static function instance($config) : Payment_Processor {
		if (!is_array($config) or !($type = @$config['type']))
			throw new Exception("Invalid payment processor configuration");
		
		if (@$instances[$type])
			return $instances[$type];
		
		$className = "Payment_Processor_" . ucfirst($type);
		try {
			return $instances[$type] = new $className($config);
		} catch (Error $e) {
			throw new Excecption("Invalid payment processor '$type'");
		}
	}
	
}
