<?php
class Controller_Auth extends Api_Controller {
	
	public static function getSessionLogin() {
		return Session::instance()->get('logged-in-user-token');
	}
	
	public static function setSessionLogin($token) {
		return Session::instance()->set('logged-in-user-token', $token);
	}
	
	public static function clearSessionLogin() {
		return Session::instance()->delete('logged-in-user-token');
	}
	
	public function action_verify() {
		$callback = $this->input()->fetch('redirect-url');
		try {
			$this->verifyAuthentication();
			if ($callback)
				return $this->redirect(self::addQueryToURL($callback, [
						'status' => true,
						'token' => self::getSessionLogin(),
				]));
			$this->send([
					"status" => true
			]);
		} catch (HTTP_Exception_403 $e) {
			if ($callback)
				return $this->redirect_to_action('select', ['redirect-url' => $callback]);
			$this->send([
					"status" => false
			]);
		}
	}

	public function action_start() {
		$this->send([
				"auth-url" => $this->startAuth($this->input()->provider ?  : 'google', $this->input()->redirect_url)
		]);
	}
	
	public function action_logout() {
		try {
			$tok = $this->verifyAuthentication();
			$tok->delete();
		} catch (Api_Exception_Unauthorized $e) {} // if we can't find a valid token, its like we logged out, right?
		self::clearSessionLogin();
		if ($this->input()->redirect_url) {
			$this->redirect($this->input()->redirect_url);
		} else {
			$this->send([ 'status' => true ]);
		}
	}
	
	public function action_passwordreset() {
		try {
			$user = Model_User::byEmail($this->input()->email);
			if (!$user->hasPassword())
				throw new Model_Exception_NotFound("User " . $user->email . " does not have a local password");
			Model_Token::remove_all($user, Model_Token::TYPE_PASSWORD_RESET);
			$token = Model_Token::persist($user, Model_Token::TYPE_PASSWORD_RESET, Time_Unit::days(1));
			$email = Twig::factory('auth/passwordreset');
			Logger::info("Starting password reset for " . $user->email . " to " . $this->input()->redirect_url);
			$email->reseturl = self::addQueryToURL($this->input()->redirect_url, ['token' => $token->token]);
			Email::send(['noreply@con-troll.org', "ConTroll"], [ $user->email, $user->name ],
					'Password reset from ConTroll', $email->__toString(), [
					"Content-Type" => "text/html"
			]);
		} catch (Model_Exception_NotFound $e) {
			// agree that the user got the password reset token
			// (because I don't want to let an attacker know that there's no such user)
		} catch (Email_Exception $e) {
			Logger::error("Problem sending email");
		}
		$this->send([ 'status' => true ]);
	}
	
	public function action_passwordchange() {
		try {
			$tok = $this->verifyAuthentication();
			if ($this->input()->password)
				$tok->user->changePassword($this->input()->password);
			else
				throw new Exception("No password specified");
			if ($tok->type == Model_Token::TYPE_PASSWORD_RESET)
				$tok->delete(); // forget a password reset token
			$this->send([ 'status' => true ]);
		} catch (Api_Exception_Unauthorized $e) {
			$this->send([ 'status' => false ]);
		} catch (Exception $e) {
			Logger::error("Got error trying to update password: " . $e->getMessage());
			$this->send([ 'status' => false, 'error' => $e->getMessage() ]);
		}
	}
	
	public function action_list() {
		$this->send(Auth::listProviders());
	}
	
	public function action_select() {
		if (!is_null($this->request->param('id')))
			$this->redirect($this->startAuth($this->request->param('id'), $this->request->query('redirect-url')));
		
		$this->view = Twig::factory('auth/accounts');
		$this->view->providers = [];
		foreach (Auth::listProviders() as $id) {
			$this->view->providers[] = [
					'id' => $id,
					'type' => Auth::getProviderType($id),
					'name' => Auth::getProviderName($id),
					'url' => '/auth/select/' . $id . '?redirect-url=' . urldecode($this->request->query('redirect-url')),
					'image' => Auth::getLoginButton($id),
					'redirecturl' => $this->request->query('redirect-url'),
			];
		}
		$this->view->error = Session::instance()->get_once('select-login-error');
		$this->view->register_email = Session::instance()->get_once('select-register-email');
		$this->auto_render = true;
	}
	
	public function action_signin() {
		try {
			$u = Model_User::byPassword($this->input()->email, $this->input()->password);
			if ($this->input()->redirect_url) {
				$this->completeAuthToApp($this->input()->redirect_url, $u->login()->token);
			} else {
				$token = $u->login()->token;
				self::setSessionLogin($token); // cache token in session for faster auth next time
				$this->send([
						"status" => true,
						"token" => $token,
				]);
			}
		} catch (Model_Exception_NotFound $e) {
			$error = "No account with that email and password found.";
			$this->errorToSelectorOrCaller($error, $this->input()->redirect_url, 401);
		}
	}
	
	public function action_register() {
		if (!$this->input()->email)
			return $this->errorToSelectorOrCaller("A valid email address is required", $this->input()->redirect_url);
			Logger::info('Auth/Register: starting to register ' . $this->input()->email . ' Checking existing user');
		try {
			Model_User::byEmail($this->input()->email);
			Logger::info('Auth/Register: found existing user');
			$this->errorToSelectorOrCaller("This email address is already registered", $this->input()->redirect_url);
		} catch (Model_Exception_NotFound $e) { } // this is the OK case
		
		if (!$this->input()->password_register)
			$this->errorToSelectorOrCaller("Password must not be empty",$this->input()->redirect_url);
		
		if (!$this->input()->isREST()) {
			// in POST form, the client has no logic and relies on us to verify that the user knows
			// their own password
			Session::instance()->set('select-register-email', $this->input()->email);
			if ($this->input()->password_register != $this->input()->password_confirm)
				$this->errorToSselector("Passwords must match", $this->input()->redirect_url);
		}
		
		$u = Model_User::persistWithPassword($this->input()->name ?: explode('@',$this->input()->email)[0], $this->input()->email,
				$this->input()->password_register);
		Logger::info('Auth/Register: saved user ' . $u->id);
		Session::instance()->set('update-user-token', $u->login()->token);
		if ($this->input()->isREST())
			$this->send([ "status" => true ]);
		else
			$this->redirect('/auth/update/' . $u->id . '?redirect-url=' . urlencode($this->input()->redirect_url));
	}
	
	public function action_update() {
		$user = new Model_User($this->request->param('id'));
		$token = Session::instance()->get('update-user-token');
		if ($user->login()->token != $token)
			throw new HTTP_Exception_403('Invalid token');
		
		if ($this->request->post('update')) {
			if (!$user->emailIsValid()) { // code for "we need to ask the user for their email address"
				$this->updateUserEmailIfValid($user, $this->request->post('email'));
			}
			
			if ($user->emailIsValid() && $user->name)
				$this->completeAuthToApp($this->request->post('redirect-url'), $user->login()->token);
		}
		
		$this->view = Twig::factory('auth/update');
		$this->view->error = Session::instance()->get_once('update-user-error');
		$this->view->user = $user;
		$this->view->redirect_url = $this->request->query('redirect-url') ?: $this->request->post('redirect-url');
		$this->auto_render = true;
	}
	
	public function action_id() {
		$tok = $this->verifyAuthentication();
		$user = $tok->user;
		$this->send([
				'id' => $user->pk(),
				'email' => $user->email,
				'name' => $user->name,
		]);
	}
	
	public function action_callback() {
		$callback = null;
		// google response parameters: state, code, authuser, prompt, session_state
		try {
			$provider = Auth::getLastProvider();
			$provider_params = [];
			foreach ($provider->getNeededQueryParams() as $query)
				$provider_params[$query] = $this->request->query($query);
			$provider->complete($provider_params);
			$u = Model_User::persist($provider->getName(), $provider->getEmail(), $provider->getProviderName(), $provider->getToken());
			$callback = $provider->getRedirectURL();
			if (!$u->emailIsValid()) {
				Session::instance()->set('update-user-token', $u->login()->token);
				$this->redirect('/auth/update/' . $u->id . '?redirect-url=' . urlencode($callback));
			}
			$response = ['status' => true, 'token' => $u->login()->token ];
		} catch (Auth_Cancelled $e) {
			$callback = $provider->getRedirectURL();
			$response = ['status' => false, 'error' => 'User cancelled' ];
		} catch (ORM_Validation_Exception $e) {
			$callback = $provider->getRedirectURL();
			$response = ['status' => false, 'error' => "Error getting name and/or email for '{$provider->getName()}','{$provider->getEmail()}'" ];
			Logger::error("Error getting name and/or email for '{$provider->getName()}','{$provider->getEmail()}'");
		} catch (Exception $e) {
			Logger::error("Unexpected error on auth callback: $e");
			if (@$provider)
				$callback = $provider->getRedirectURL();
			$response = ['status' => false, 'error' => "$e" ];
		}
		
		// TODO: handle client side only without redirects
		if ($callback) {
			if ($response['status'])
				$this->completeAuthToApp($callback, $response['token']);
			else
				$this->failAuthToApp($callback, $response['error']);
		} else
			$this->send($response);
	}
	
	private function updateUserEmailIfValid(Model_User $user, $email) {
		if (!Valid::email($email))
			return false;
		// check that its not conflicting
		try {
			Model_User::byEmail($email);
			Session::instance()->set('update-user-error', 'An account with that email already exists.');
			return false;
		} catch (Model_Exception_NotFound $e) {}
		$user->email = $email;
		$user->save();
	}
	
	private function completeAuthToApp($callback, $token) {
		Logger::debug('Setting session token to :token',[':token' => $token]);
		self::setSessionLogin($token); // cache token in session for faster auth next time
		$this->redirect(self::addQueryToURL($callback, [
				'status' => true,
				'token' => $token,
		]));
	}
	
	private function failAuthToApp($callback, $error) {
		$this->redirect(self::addQueryToURL($callback, [
				'status' => false,
				'error' => $error,
		]));
	}
	
	private function errorToSelectorOrCaller($error_message, $redirect_url, $status = 400) {
		if ($redirect_url) {
			Session::instance()->set('select-login-error', $error_message);
			$this->redirect_to_action('select', ['redirect-url' => $redirect_url]);
		} else {
			$this->response->status($status);
			$this->send([ 'status' => false, 'error' => $error_message]);
		}
	}
	
	private function startAuth($provider, $redirect_url) {
		return Auth::getProvider($provider, strtolower($this->action_url('callback', true)))->getAuthenticationURL($redirect_url);
	}

}
