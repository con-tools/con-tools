<?php

interface Email_Interface {
	
	/**
	 * Send an email
	 * @param string|array $from Address of the sender, if a string - is taken to be the plain email address;
	 * 	if an string array is provided, the first element is taken as the plain email address and the second as the sender name.
	 * @param string|array $to Address of the recipient, if a string - is taken to be the plain email address;
	 * 	if an string array is provided, the first element is taken as the plain email address and the second as the recipient name.
	 * @param string $subject Subject line of the email
	 * @param string $body Content of the email
	 * @param array $headers Additional headers as an array of key value pairs 
	 */
	function send($from, $to, $subject, $body, $headers);
	
}
