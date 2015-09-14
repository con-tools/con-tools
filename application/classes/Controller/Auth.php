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
		$data = json_decode($this->request->body()) ?  : [ ];
		$this->send([ 
				"auth-url" => Auth::getProvider(@$data ['provider'] ?  : 'google', 
						strtolower($this->action_url('callback', true)))->getAuthenticationURL(@$data['redirect-url'])
		]);
	}
	
	public function action_logout() {
		$tok = $this->verifyAuthentication();
		$tok->delete();
	}
	
	public function action_list() {
		$this->send(Auth::listProviders());
	}
	
	public function action_test() {
		var_dump(Model_User::byEmail('oded@geek.co.il'));
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
			$response = ['status' => false, 'error' => "$e" ];
		}
		
		if ($callback) {
			$url = parse_url($callback);
			$query = explode('&',$url['query']);
			foreach ($response as $key => $val)
				$query[] = urlencode($key) . '=' . urlencode($val);
			$url['query'] = join('&', $query);
			$this->redirect($this->buildUrl($url));
		} else
			$this->send($response);
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
		$url .= $spec['path'];
		if (@$spec['query'])
			$url .= "?{$spec['query']}";
		if (@$spec['fragment'])
			$url .= "#{$spec['fragment']}";
		return $url;
	}

}
