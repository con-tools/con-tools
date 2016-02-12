<?php

class Email_Swiftmailer implements Email_Interface {

	var $transport;
	var $mailer;
	
	public function __construct($config) {
		$this->transport = Swift_SmtpTransport::newInstance($config['host'], $config['port'], $config['tls'] ? 'tls' : null);
		$this->transport->setUsername($config['user']);
		$this->transport->setPassword($config['password']);
		$this->mailer = Swift_Mailer::newInstance($this->transport);
	}
	
	public function send($from, $to, $subject, $body, $headers) {
		$message = Swift_Message::newInstance()
			->setSubject($subject)
			->setFrom(is_array($from) ? [ $from[0] => $from[1] ] : $from)
			->setTo(is_array($to) ? [ $to[0] => $to[1] ] : $to)
			->setBody($body);
		foreach ($headers as $name => $value)
			$message->getHeaders()->addTextHeader($name, $value);
		if ($this->mailer->send($message) == 0)
			throw new Email_Exception("Failed to send email to " . $to);
	}
}
