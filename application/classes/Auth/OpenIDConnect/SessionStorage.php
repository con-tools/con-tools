<?php

class Auth_OpenIDConnect_SessionStorage implements OpenIDSessionStorageIF {
	
	/**
	 * {@inheritDoc}
	 * @see OpenIDSessionStorageIF::storeNonce()
	 */
	public function storeNonce($nonce) {
		Session::instance()->set('auth-openid-connect-nonce', $nonce);
	}

	/**
	 * {@inheritDoc}
	 * @see OpenIDSessionStorageIF::getNonce()
	 */
	public function getNonce() {
		return Session::instance()->get('auth-openid-connect-nonce', null);
	}

	/**
	 * {@inheritDoc}
	 * @see OpenIDSessionStorageIF::storeState()
	 */
	public function storeState($state) {
		Session::instance()->set('auth-openid-connect-state', $state);
	}

	/**
	 * {@inheritDoc}
	 * @see OpenIDSessionStorageIF::getState()
	 */
	public function getState() {
		return Session::instance()->get('auth-openid-connect-state', null);
	}

	/**
	 * {@inheritDoc}
	 * @see OpenIDSessionStorageIF::clear()
	 */
	public function clear() {
		Session::instance()->delete('auth-openid-connect-state');
		Session::instance()->delete('auth-openid-connect-nonce');
	}

}
