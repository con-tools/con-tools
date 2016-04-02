<?php

class Model_Organization_Member extends ORM {
	
	protected $_belongs_to = [
			'organizer' => [],
			'user' => [],
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'organizer_id' => [],
			'user_id' => [],
			// data fields
			'membership' => []
	];
	
	/**
	 * Create and store a new organization membership subscription
	 * @param Model_Organizer $organizer Organization that organizes a convention
	 * @param Model_User $user User that is the member
	 * @param string $membership Membership code/number/registration etc'
	 * @return Model_Organization_Member
	 */
	public static function persist(Model_Organizer $organizer, Model_User $user, string $membership) : Model_Organization_Member {
		$o = new Model_Organization_Member();
		$o->organizer = $organizer;
		$o->user = $user;
		$o->save();
		return $o;
	}
	
	/**
	 * Retrieve all members of the specified organization
	 * @param Model_Organizer $organizer Organization whose members are to be retrieved
	 * @return Database_Result
	 */
	public static function getByOrganizer(Model_Organizer $organizer) : Database_Result {
		return (new Model_Organization_Member())->where('organizer_id', '=', $organizer->pk())->find_all();
	}
	
	/**
	 * Retrieve all members of organizing organizations for the specified convention
	 * @param Model_Convention $con Convention where members should be retrieved
	 * @return Database_Result
	 */
	public static function getByConvention(Model_Convention $con) : Database_Result {
		return (new Model_Organization_Member())->with('organizer')->where('convention_id', '=', $con->pk())->find_all();
	}
}
