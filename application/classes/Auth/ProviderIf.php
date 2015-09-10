<?php

/**
 * API for authentication provider implementations
 * @author odeda
 */
interface Auth_ProviderIf {
	/**
	 * Find a redirect URL to send to the client
	 */
	public function getAuthenticationURL();
	
	/**
	 * Complete the authentication process
	 * @param string $code
	 * @param string $state
	 */
	public function complete($code, $state);
	
	/**
	 * Retrieve the authenticated user name
	 * @return string Full name
	 */
	public function getName();
	
	/**
	 * Retrieve the authenticated user email
	 * @return string Email
	 */
	public function getEmail();
	
	/**
	 * Get the provider configuration name
	 */
	public function getProviderName();
	
	/**
	 * Retrieve the underlying authentication/authorization token in text form
	 * @return string token
	 */
	public function getToken();
}
