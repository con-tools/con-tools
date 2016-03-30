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
		$id = base64_encode(serialize(['i'=>$id,'f'=>$fields]));
		return URL::base().Route::get('default')->uri([
				'controller' => 'checkout',
				'action' => 'callback',
				'id' => $id,
		]);
	}
	
	/**
	 * Checkout processing
	 */
	public function action_index() {
		if ($this->request->method() == 'POST')
			return $this->startCheckout();
		elseif ($this->request->method() == 'GET')
			return $this->renderDummyCart();
		else
			throw new Api_Exception_InvalidInput($this, "This API supports only POST requestes");
	}
	
	/**
	 * Called by the payment processor service to trigger our payment processor adapter callbacks
	 */
	public function action_callback() {
		$calldata = @unserialize(base64_decode($this->request->param('id')));
		if (!$calldata)
			throw new Exception("Invalid callback ID. Please contact the administrator"); // shouldn't happen
		$id = $calldata['i'];
		$fields = $calldata['f'];
		try {
			$con = Model_Convention::bySlug($id);
			$redirect = $con->getPaymentProcessor()->handleCallback($this->input(), $fields);
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
		return $this->response->body($convention->getPaymentProcessor()->createTransactionHTML($sale, $data->ok, $data->fail));
	}
	
	private function renderDummyCart() {
		$convention = $this->verifyConventionKey();
		try {
			$user = $this->verifyAuthentication()->user;
			$view = Twig::factory('payment/cart');
			$view->convention_key = $convention->getPublicKey();
			$view->tickets = Model_Ticket::shoppingCart($convention, $user);
			$view->total = Model_Sale::computeTotal($view->tickets);
			$view->baseurl = URL::base();
			return $this->response->body($view->render());
		} catch (Api_Exception_Unauthorized $e) {
			return $this->redirect('/auth/select?redirect-url=' . urlencode(URL::base(). $this->request->uri() . URL::query()));
		}
	}
}
