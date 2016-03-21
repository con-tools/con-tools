<?php

class Input {
	
	/**
	 * @var  Request  Request that created the controller
	 */
	private $_request = null;
	
	/**
	 * Whether the request is using JSON REST
	 * @var boolean
	 */
	private $_rest = null;
	
	/**
	 * Input data
	 * @var array
	 */
	private $_data = null;
	const ISSET_DETECTION_MAGIC_VALUE = 0xDE7EC7;
	
	public function __construct($request) {
		$this->_rest = $request->headers('Content-Type') == 'application/json';
		$this->_request = $request;
		$this->_data = $this->isREST() ? json_decode($this->_request->body(), true) : array_merge(
				$this->_request->query(), $this->_request->post());
	}
	
	public function isREST() {
		return $this->_rest;
	}
	
	public function __get($field) {
		return $this->fetch($field) ?: $this->fetch(str_replace('_', '-', $field));
	}
	
	public function __isset($field) {
		return $this->fetch($field, ISSET_DETECTION_MAGIC_VALUE) != ISSET_DETECTION_MAGIC_VALUE;
	}
	
	public function fetch($field, $default = null) {
		return Arr::path($this->_data, $field, $default);
	}
}