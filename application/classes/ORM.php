<?php

class ORM extends Kohana_ORM {
	
	protected $_columns = [];
	
	/**
	 * (non-PHPdoc)
	 * @see Kohana_ORM::set()
	 */
	public function set($column, $value) {
		// handle type conversions, if the model specifies it
		$field_def = @$this->_columns[$column];
		if (is_array($field_def)) {
			switch (@$field_def['type']) {
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
			switch (@$field_def['type']) {
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
	
	public function compile() {
		$this->_build(Database::SELECT);
		return $this->_db_builder->compile();
	}
	
	public function save(Validation $validation = NULL) {
		try {
			return parent::save($validation);
		} catch (Database_Exception $e) {
			if (strstr($e->getMessage(), 'Duplicate entry'))
					throw new Api_Exception_Duplicate(null,"Duplicate " . $this->_table_name);
			throw $e;
		}
	}
	
	public static function gen_slug($title) {
		return strtolower(preg_replace('/[^a-zA-Zא-ת0-9]+/', '-', $title));
	}
	
	/**
	 * Return a JSON friendly array presentation of the data
	 * based on Kohana_ORM#as_array()
	 */
	public function for_json() {
		$ar = $this->as_array();
		$out = [];
		foreach ($ar as $key => $value) {
			$out[str_replace('_', '-', $key)] = $value;
		}
		return $out;
	}
}
