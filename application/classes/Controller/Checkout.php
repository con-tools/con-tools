<?php

/**
 * Implement checkout feature for a convention
 * @author odeda
 */
class Controller_Checkout extends Api_Controller {

	/**
	 * Assuming this controller gets called by the default route,
	 * generate a self referencing URL that also encodes the specified
	 * data fields.
	 * @param array $fields data for URL query string
	 */
	public static function getCallbackURL($id, $fields) {
		$query_string = [];
		foreach ($fields as $key => $val)
			$query_string[] = urlencode($key) . '=' . urlencode($val);
		return Route::get('default')->uri([
				'controller' => 'checkout',
				'action' => 'callback',
				'id' => $id,
		]) . '?' . join('&', $query_string);
	}
	
	/**
	 * Checkout processing
	 */
	public function action_index() {
		if ($this->request->method() == 'POST')
			$this->startCheckout();
		else
			throw new Api_Exception_InvalidInput($this, "This API supports only POST requestes");
	}
	
	/**
	 * Called by the payment processor service to trigger our payment processor adapter callbacks
	 */
	public function action_callback() {
		try {
			$con = Model_Convention::bySlug($this->request->param('id'));
			$redirect = $con->getPaymentProcessor()->handleCallback($this->input());
			if (!is_string($redirect))
				throw new Exception("Payment processing adapter returned ". print_r($redirect, true));
			return $this->redirect($redirect);
		} catch (Model_Exception_NotFound $e) {
			echo "Failed to find callback implementation. Please contact the administrator.";
		}
	}
	
	private function startCheckout() {
		$convention = $this->verifyConventionKey();
		$user = $this->verifyAuthentication()->user;
		$data = $this->input();
		
		if (!$data->ok || !$data->fail)
			throw new Api_Exception_InvalidInput($this, "Checkout requires an 'ok' URL and a 'fail' URL");
		
		$sale = Model_Sale::persist($user, $convention);
		$html = $convention->getPaymentProcessor()->createTransactionHTML($sale, $data->ok, $data->fail);
		echo $html;
	}
}
