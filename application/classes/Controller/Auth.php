<?php
class Controller_Auth extends Api_Controller {
	
	public function action_verify() {
		try {
			$this->verifyAuthentication();
			$this->send([ 
					"status" => true 
			]);
		} catch (HTTP_Exception_403 $e) {
			$this->send([ 
					"status" => false 
			]);
		}
	}

	public function action_start() {
		$data = json_decode($this->request->body(), true) ?  : [ ];
		$this->send([ 
				"auth-url" => $this->startAuth(@$data['provider'] ?  : 'google', @$data['redirect-url'])
		]);
	}
	
	public function action_logout() {
		$tok = $this->verifyAuthentication();
		$tok->delete();
		$this->send([ 'status' => true ]);
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
			$u = Model_User::byPassword($this->request->post('email'), $this->request->post('password'));
			$this->completeAuthToApp($this->request->post('redirect-url'), $u->login()->token);
		} catch (Model_Exception_NotFound $e) {
			$this->errorToSselector("No account with that email and password found.", $this->request->post('redirect-url'));
		}
	}
	
	public function action_register() {
		$email = $this->request->post('email');
		error_log('starting to register ' . $email);
		if (!$email)
			$this->errorToSselector("A valid email address is required", $this->request->post('redirect-url'));
		Session::instance()->set('select-register-email', $email);
		error_log('Checking existing user');
		try {
			Model_User::byEmail($email);
			error_log('found existing user');
			$this->errorToSselector("This email address is already registered", $this->request->post('redirect-url'));
		} catch (Model_Exception_NotFound $e) { } // this is the OK case
		error_log('checking passwords');
		if (!$this->request->post('password-register'))
			$this->errorToSselector("Password must not be empty",$this->request->post('redirect-url'));
		if ($this->request->post('password-register') != $this->request->post('password-confirm'))
			$this->errorToSselector("Passwords must match", $this->request->post('redirect-url'));
		error_log('passwords fine');
		$u = Model_User::persistWithPassword(explide('@',$email)[0], $email, $this->request->post('password-register'));
		error_log('saved user ' . $u->id);
		Session::instance()->set('update-user-token', $u->login()->token);
		error_log('calling update');
		$this->redirect('/auth/update/' . $u->id . '?redirect-url=' . urlencode($this->request->post('redirect-url')));
	}
	
	public function action_update() {
		$user = new Model_User($this->request->param('id'));
		$token = Session::instance()->get('update-user-token');
		if ($user->login()->token != $token)
			throw new HTTP_Exception_403('Invalid token');
		
		if ($this->request->post('update')) {
			if ($user->email == '-' && // code for "we need to ask the user for their email address"
				Valid::email($this->request->post('email'))) {
				$user->email = $this->request->post('email');
				$user->save();
			}
			
			if ($user->email != '-' && $user->name)
				$this->completeAuthToApp($this->request->post('redirect-url'), $user->login()->token);
		}
		
		$this->view->error = Session::instance()->get_once('update-user-error');
		$this->view = Twig::factory('auth/update');
		$this->view->user = $user;
		$this->view->redirect_url = $this->request->query('redirect-url') ?: $this->request->post('redirect-url');
		$this->auto_render = true;
	}
	
	public function action_id() {
		$tok = $this->verifyAuthentication();
		$user = $tok->user;
		$this->send([
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
			$o = Model_User::persist($provider->getName(), $provider->getEmail(), $provider->getProviderName(), $provider->getToken());
			$callback = $provider->getRedirectURL();
			$response = ['status' => true, 'token' => $o->login()->token ];
		} catch (Auth_Cancelled $e) {
			$callback = $provider->getRedirectURL();
			$response = ['status' => false, 'error' => 'User cancelled' ];
		} catch (Exception $e) {
			throw $e;
			$response = ['status' => false, 'error' => "$e" ];
		}
		
		if ($callback) {
			if ($response['status'])
				$this->completeAuthToApp($callback, $response['token']);
			else
				$this->failAuthToApp($callback, $response['error']);
		} else
			$this->send($response);
	}
	
	private function completeAuthToApp($callback, $token) {
		$url = parse_url($callback);
		$query = explode('&',@$url['query'] ?: '');
		$query[] = urlencode('status') . '=' . urlencode(true);
		$query[] = urlencode('token') . '=' . urlencode($token);
		$url['query'] = join('&', $query);
		$this->redirect($this->buildUrl($url));
	}
	
	private function failAuthToApp($callback, $error) {
		$url = parse_url($callback);
		$query = explode('&',@$url['query'] ?: '');
		$query[] = urlencode('status') . '=' . urlencode(false);
		$query[] = urlencode('error') . '=' . urlencode($error);
		$url['query'] = join('&', $query);
		$this->redirect($this->buildUrl($url));
	}
	
	private function errorToSselector($error_message, $redirect_url) {
		sleep(1500); // make it a bit harder to bruteforce a password
		Session::instance()->set('select-login-error', $error_message);
		$this->redirect_to_action('select', ['redirect-url' => $redirect_url]);
	}
	
	private function startAuth($provider, $redirect_url) {
		return Auth::getProvider($provider, strtolower($this->action_url('callback', true)))->getAuthenticationURL($redirect_url);
	}
	
	private function buildUrl($spec) {
		$url = "{$spec['scheme']}://";
		if (@$spec['user']) {
			$url .= $spec['user'];
			if (@$spec['pass'])
				$url .= ":{$spec['pass']}";
			$url .= "@";
		}
		$url .= $spec['host'];
		if (@$spec['port'])
			$url .= ":{$spec['port']}";
		$url .= @$spec['path'] ?: '/' ;
		if (@$spec['query'])
			$url .= "?{$spec['query']}";
		if (@$spec['fragment'])
			$url .= "#{$spec['fragment']}";
		return $url;
	}

}
