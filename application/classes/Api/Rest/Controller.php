<?php

/**
 * Implementation of a REST controller that accepts CRUD operations using HTTP verbs.
 *
 * Currently this implementation assumes that a convention has been identified. If you
 * need to support convention-less operations, don't use this as a base
 *
 * @author odeda
 *
 */
abstract class Api_Rest_Controller extends Api_Controller {
	
	/**
	 * Identified, and possibly authenticated convention
	 * @var Model_Convention $convention
	 */
	protected $convention = null;
	
	/**
	 * Logged in users, if authenticated
	 * @var Model_User $user
	 */
	protected $user = null;
	
	public function action_index() {
		$this->convention = $this->verifyConventionKey();
		try {
			$this->user = $this->verifyAuthentication()->user;
		} catch (Api_Exception_Unauthorized $e) {
			// some APIs allow no user auth
		}
		
		switch ($this->request->method()) {
			case 'POST':
				$obj = $this->create();
				if (is_null($obj))
					$this->send([ 'status' => false ]);
				elseif ($obj instanceof ORM)
					$this->send([ 'status' => true, 'id' => $obj->pk() ]);
				elseif (is_array($obj))
					$this->send(array_merge([ 'status' => true ], $obj));
				else
					$this->send([ 'status' => true ]);
				return;
			case 'GET':
				if ($this->request->param('id')) {
					$this->send(
						$this->retrieve($this->request->param('id'))
					);
				} else {
					$this->send($this->catalog());
				}
				return;
			case 'PUT':
				$ret = $this->update($this->request->param('id'));
				if (is_bool($ret)) // updater just want to say "ok" (or not)
					$data = [ 'status' => $ret ];
				else // updater wants to show what its done
					$data = array_merge([ 'status' => true], $ret);
				$this->send($data);
				return;
			case 'DELETE':
				$this->send([
					'status' => $this->delete($this->request->param('id'))
				]);
				return;
			default:
				throw new Exception("Invalid operation {$this->request->method()}");
		}
	}
	
	/**
	 * Create a new record
	 * @param stdClass $data Data to create the record
	 * @return ORM Model object created
	 */
	abstract protected function create();
	
	/**
	 * Retrieve an existing record by ID
	 * @param int $id record ID
	 * @return stdClass Record data
	 */
	abstract protected function retrieve($id);
	
	/**
	 * Update an existing record
	 * @param int $id record ID
	 * @param stdClass $data Data to update the record
	 * @return boolean Whether the create succeeded
	 */
	abstract protected function update($id);
	
	/**
	 * Delete an existing record
	 * @param int $id record ID
	 * @return boolean Whether the delete succeeded
	 */
	abstract protected function delete($id);
	
	/**
	 * Retrieve the catalog of entities
	 * @return array
	 */
	abstract protected function catalog();

	/**
	 * Helper method for common paradigm of checking if either a user is a manager or we have
	 * a secret convention auth, to gain system level access to convention resources
	 * @return boolean whether system level access should be granted
	 */
	protected function systemAccessAllowed() {
		return $this->convention->isManager($this->user) || $this->convention->isAuthorized();
	}
	
	/**
	 * Implement the common behavior of getting a user
	 * from either a user id or a user email (but never both) and
	 * verifying everything
	 * @param int $user_id numeric user ID from the database
	 * @param string $email user's login email
	 */
	protected function loadUserByIdOrEmail($user_id, $email) {
		if ($user_id and $email)
			throw new Api_Exception_InvalidInput($this, "Please provide either an `id` or `email` but not both");
		if ($user_id) {
			$user = new Model_User($user_id);
			if (!$user->loaded())
				throw new Api_Exception_InvalidInput($this, "Invalid user specified");
			return $user;
		}
		
		if ($email) {
			try {
				return Model_User::byEmail($email);
			} catch (Model_Exception_NotFound $e) {
				throw new Api_Exception_InvalidInput($this, "Invalid user specified");
			}
		}
		
		throw new Api_Exception_InvalidInput($this, "Invalid user specified");
	}
	
	protected function parseDateTime($value) {
		if (!$value)
			return false;
		
		try {
			if (is_numeric($value))
				return new DateTime("@" . $value);
			else
				return new DateTime($value); // lets hope the SPL can do something with this
		} catch (Exception $e) {
			error_log("Error parsing time value '$value': ". $e->getMessage());
			return false;
		}
	}
	
}
