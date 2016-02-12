<?php

class Email_Native implements Email_Interface {
	
	public function __construct($config) {
	}
	
	public function send($from, $to, $subject, $body, $headers) {
		if (is_array($from))
			$from = $from[1] . "<" . $from[0] . ">";
		if (is_array($to))
			$to = $to[1] . "<" . $to[0] . ">";
			$headers = [ "From: $from" ];
		foreach ($headers as $name => $value) {
			$headers[] = "$name: $value";
		}
		$res = mail($to, $subject, $body, join("\r\n", $headers) . "\r\n");
		if (!$res)
			throw new Email_Exception("Error sending email");
	}
	
}
