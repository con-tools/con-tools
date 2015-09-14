<?php

class ORM extends Kohana_ORM {
	
	protected $_columns = [];
	
	public function __construct($id = NULL) {
		$this->_initialize();
		$this->_primary_key = $this->object_name() . '_id';
		parent::__construct($id);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Kohana_ORM::set()
	 */
	public function set($column, $value) {
		// handle type conversions, if the model specifies it
		$field_def = @$this->_columns[$column];
		if (is_array($field_def)) {
			switch ($field_def['type']) {
				case 'DateTime':
					$value = $this->sqlize($value);
					break;
			}
		}
		
		return parent::set($column, $value);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Kohana_ORM::get()
	 */
	public function get($column) {
		$value = parent::get($column);
		// handle type conversions, if the model specifies it
		$field_def = @$this->_columns[$column];
		if (is_array($field_def)) {
			switch ($field_def['type']) {
				case 'DateTime':
					$value = $this->unsqlize($value);
					break;
			}
		}
		return $value;
	}
	
	/**
	 * convert date/time values to sql date
	 * @param mixed $value
	 */
	public function sqlize($value) {
		if (is_numeric($value))
			return date("Y-m-d H:i:s", $value);
		if ($value instanceof DateTime)
			return $this->sqlize($value->getTimestamp());
		return $value;
	}
	
	/**
	 * Convert SQL date to DateTime value
	 * @param string $value
	 */
	public function unsqlize($value) {
		return DateTime::createFromFormat("Y-m-d H:i:s", $value);
	}
}
