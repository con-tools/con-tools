<?php

/**
 * Abstract adapter to payment processor implementations
 * @author odeda
 */
abstract class Payment_Processor {
	
	private static $instances = [];
	
	protected $config;
		/**
		 * Convention using this processor
		 * @var Model_Convention
		 */
	protected $convention;
	
	public static function instance(Model_Convention $convention, $config) : Payment_Processor {
		if (@$instances[$convention->slug])
			return $instances[$convention->slug];
		
		if (!is_array($config) or !($type = @$config['type']))
			throw new Exception("Invalid payment processor configuration");
		
		$className = "Payment_Processor_" . ucfirst($type);
		try {
			return $instances[$convention->slug] = new $className($convention, $config);
		} catch (Error $e) {
			throw new Excecption("Invalid payment processor '$type'");
		}
	}
	
	protected function __construct(Model_Convention $con, $config) {
		$this->config = $config;
		$this->convention = $con;
	}
	
	/**
	 * Genearte a URL to call back this payment processor, through
	 * the checkout controller, for use as callback URL for a payment
	 * processor.
	 * @param array $fields query data that can be encoded and submitted back by the processor.
	 */
	protected function generateCallbackURL($fields) {
		return Controller_Checkout::getCallbackURL($this->convention->slug, $fields);
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
	
	/**
	 * Called by the payment processor service when the callback gets called
	 * @param Input $request request data submitted to the callback endpoint. this
	 *   should contain the fields submitted to {@link #generateCallbackURL()}
	 */
	abstract public function handleCallback(Input $request);
}
