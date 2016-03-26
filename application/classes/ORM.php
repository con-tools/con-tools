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
				case 'boolean':
					$value = $value ? '1' : '0';
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
				case 'boolean':
					$value = is_numeric($value) ? ($value != 0) : $value;
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
			if ($value instanceof DateTime) { // format DateTime for consumption
				$value = $value->format(DateTime::ATOM);
			}
			$out[str_replace('_', '-', $key)] = $value;
		}
		return $out;
	}
	
	/**
	 * Helper call to convert an array or Database_Result to an array of "for_json" objects
	 * @param array|Database_Result $result
	 */
	public static function result_for_json($result, $for_json_method = 'for_json') {
		if ($result instanceof Database_Result)
			$result = $result->as_array();
		return array_map(function(ORM $ent) use($for_json_method) {
			return $ent->{$for_json_method}();
		}, $result);
	}
}
