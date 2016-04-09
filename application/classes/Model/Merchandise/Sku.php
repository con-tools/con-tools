<?php

class Model_Merchandise_Sku extends ORM {
	
	protected $_belongs_to = [
			'convention' => [],
	];
	
	protected $_has_many = [
			'purchases' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'convention_id' => [],
			// data fields
			'title' => [],
			'code' => [],
			'created_time' => [ 'type' => 'DateTime' ],
			'description' => [],
			'price' => [],
	];
	
	public static function byCodeOrId($code) : Model_Merchandise_Sku {
		$sku = (new Model_Merchandise_Sku())->where('code', 'like', $code)->find();
		if ($sku->loaded())
			return $sku;
		// could be an ID to an SKU?
		$sku = new Model_Merchandise_Sku($code);
		if ($sku->loaded())
			return $sku;
		throw new Model_Exception_NotFound(); // nah
	}
	
	public static function persist(Model_Convention $con, string $title, string $code, $price, $description = null) : Model_Merchandise_Sku {
		$o = new Model_Merchandise_Sku();
		$o->convention = $con;
		$o->created_time = new DateTime();
		$o->title = $title;
		$o->code = $code;
		$o->description = $description;
		$o->price = $price;
		return $o->save();
	}

	/**
	 * Generate a query for all merchandise belonging to a convention
	 * @param Model_Convention $con convention to list for
	 * @return ORM a model object with the query loaded
	 */
	public static function queryForConvention(Model_Convention $con) : ORM {
		$query = (new Model_Merchandise_Sku())->where('convention_id', '=', $con->pk());
		return $query;
	}
	
	public function for_json() {
		return array_merge(array_filter(parent::for_json(),function($key){
			return !in_array($key, ['convention-id']);
		},ARRAY_FILTER_USE_KEY),[
				'convention' => $this->convention->for_json(),
		]);
	}
}
