<?php

use StephaneCoinon\Papertrail\Base as Papertrail;

class Log_Papertrail extends Log_Writer {
	
	/**
	 * @var \Monolog\Logger
	 */
	private $logger;
	
	public function __construct() {
		$this->logger = Papertrail::boot("logs4.papertrailapp.com",22965);
	}
	
	public function write(array $messages) {
		$filtered_messages = $this->filter($messages);

		if (empty($filtered_messages))
		{
			return;
		}
		foreach ($filtered_messages as $message)
		{
			$this->logger->addInfo($this->format_message($message));
		}
	}
	
}