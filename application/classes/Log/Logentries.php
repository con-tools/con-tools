<?php
require_once dirname(__FILE__) . '/LeLogger.php';

class Log_Logentries extends Log_Writer {
	
	private $log;
	private $token;
	
	public function __construct($config) {
		$LOGENTRIES_TOKEN = '51d2b1cb-d44f-4fd8-83d7-f4058253855e';
		$DATAHUB_ENABLED = false;
		$DATAHUB_IP_ADDRESS = "";
		$DATAHUB_PORT = 10000;
		$HOST_NAME_ENABLED = true;
		$HOST_NAME = null;
		$HOST_ID = 'heroku';
		$ADD_LOCAL_TIMESTAMP = true;
		$this->log = LeLogger::getLogger(
				$this->token = $config['token'], true, false, LOG_DEBUG, false, '', 10000, $config['hostid'],
				null, true, false);
	}

	public function write(array $messages) {
		if (empty($messages))
			return;
		
		foreach ($messages as $message) {
			$text = $this->format_message($message, "body");
			switch ($message['level']) {
				case \Psr\Log\LogLevel::EMERGENCY:
					return $this->log->Emergency($text);
				case \Psr\Log\LogLevel::ALERT:
					return $this->log->Alert($text);
				case \Psr\Log\LogLevel::CRITICAL:
					return $this->log->Critical($text);
				case \Psr\Log\LogLevel::ERROR:
					return $this->log->Error($text);
				case \Psr\Log\LogLevel::WARNING:
					return $this->log->Warning($text);
				case \Psr\Log\LogLevel::NOTICE:
					return $this->log->Notice($text);
				case \Psr\Log\LogLevel::INFO:
					return $this->log->Info($text);
				case \Psr\Log\LogLevel::DEBUG:
					return $this->log->Debug($text);
				default:
					return $this->log->Info($text);
			}
		}
	}
}

