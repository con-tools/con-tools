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
					'url' => '/auth/select/' . $id . '?redirect-url=' . urldecode($this->request->query('redirect-url')),
					'image' => Auth::getLoginButton($id),
			];
		}
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
			$provider->complete($this->request->query('code'), $this->request->query('state'));
			$o = Model_User::persist($provider->getName(), $provider->getEmail(), $provider->getProviderName(), $provider->getToken());
			$callback = $provider->getRedirectURL();
			$response = ['status' => true, 'token' => $o->login()->token ];
		} catch (Exception $e) {
			throw $e;
			$response = ['status' => false, 'error' => "$e" ];
		}
		
		if ($callback) {
			$url = parse_url($callback);
			$query = explode('&',@$url['query'] ?: '');
			foreach ($response as $key => $val)
				$query[] = urlencode($key) . '=' . urlencode($val);
			$url['query'] = join('&', $query);
			$this->redirect($this->buildUrl($url));
		} else
			$this->send($response);
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
