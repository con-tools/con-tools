<?php

/**
 * API for authentication provider implementations
 * @author odeda
 */
interface Auth_ProviderIf {
	/**
	 * Find a redirect URL to send to the client
	 */
	public function getAuthenticationURL($redirect_url);
	
	/**
	 * List the query parameters that we expect to get in the callback
	 * @return Array of query parameter names
	 */
	public function getNeededQueryParams();
	
	/**
	 * Complete the authentication process
	 * @param Array $params named array of query string parameter values delivered to the callback URL
	 */
	public function complete($params);
	
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
	
	/**
	 * Retrieve the URL stored by the authenticating user agent, where it wants to be called after
	 * authentication completes
	 */
	public function getRedirectURL();
}
