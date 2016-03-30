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
	
	/**
	 * Generate HTML for a transaction, that is used to redirect the client to
	 * the payment processor transaction fulfillment page.
	 *
	 * This method accepts a set of tickets and either throws an exception
	 * if it can't handle them,
	 *
	 * @param array $tickets list of Model_Ticket to process
	 */
	abstract public function createTransactionHTML(Model_Sale $sale, $okurl, $failurl);
	
}
