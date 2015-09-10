<?php

class ORM extends Kohana_ORM {
	
	protected $_fields = [];
	
	/**
	 * (non-PHPdoc)
	 * @see Kohana_ORM::set()
	 */
	public function set($column, $value) {
		// handle type conversions, if the model specifies it
		$field_def = @$this->_fields[$column];
		if (is_array($field_def)) {
			switch ($field_def['type']) {
				case 'DateTime':
					$value = sqlize($value);
					break;
			}
		}
		
		return parent::set($column, $value);
	}
	
	/**
	 * convert date/time values to sql date
	 * @param mixed $value
	 */
	public function sqlize($value) {
		if (is_numeric($value))
			return date("Y-m-d H:i:s", $value);
		if ($value instanceof DateTime)
			return sqlize($value->getTimestamp());
		return $value;
	}
}
