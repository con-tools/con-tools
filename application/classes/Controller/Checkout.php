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
			if ($redirect === true)
				return $this->response->body('OK');
			if (!is_string($redirect))
				throw new Exception("Payment processing adapter returned ". print_r($redirect, true));
			return $this->redirect($redirect);
		} catch (Model_Exception_NotFound $e) {
			echo "Failed to find callback implementation. Please contact the administrator.";
		}
	}
	
	public function action_cashout() {
		$convention = $this->verifyConventionKey();
		$cashier = $this->verifyAuthentication()->user;
		if (!$convention->isManager($cashier))
			throw new Api_Exception_Unauthorized($this, "Not allowed to perform cash checkout!");
		if (!$this->request->method() == 'POST')
			throw new Api_Exception_InvalidInput($this, "Cache checkout must be post");
		$received = (float)$this->input()->amount;
		$user = $this->loadUserByIdOrEmail($this->input()->user);
		$sale = Model_Sale::persist($user, $convention, $cashier);
		$total = $sale->getTotal();
		if ($total == $received)
			Logger::info("Cashout, received: {$received} through cashier {$cashier} for ${user} on {$sale}");
		else // TODO: figure out how to handle partial cashouts
			Logger::warn("Cashout, received incomplete payout of {$received} through cashier {$cashier} for ${user} on {$sale}, clearing anyway");
		$sale->authorized("cashout:" . $cashier->name);
		$this->send([ 'status' => true ]);
	}
	
	private function startCheckout() {
		$convention = $this->verifyConventionKey();
		$user = $this->verifyAuthentication()->user;
		$data = $this->input();
		
		if (!$data->ok || !$data->fail)
			throw new Api_Exception_InvalidInput($this, "Checkout requires an 'ok' URL and a 'fail' URL");
		
		$cashier = null;
		if ($data->user && $convention->isManager($user)) { // let a cashier checkout another user
			$cashier = $user;
			$user = $this->loadUserByIdOrEmail($data->user);
			Logger::info("Starting CC checkout for {$user} by cashier {$cashier}");
		}
		
		$sale = Model_Sale::persist($user, $convention, $cashier);
		if (($total = $sale->getTotal()) == 0) {
			Logger::info("User ".$user->pk()." completes sale with 0 cost");
			$sale->authorized("internal:zero-transaction");
			return $this->redirect($data->ok);
		} else {
			Logger::info("User ".$user->pk()." starts sale with total cost $total");
			return $this->response->body($convention->getPaymentProcessor()->createTransactionHTML($sale, $data->ok, $data->fail));
		}
	}
	
	private function renderDummyCart() {
		$convention = $this->verifyConventionKey();
		try {
			$user = $this->verifyAuthentication()->user;
			$view = Twig::factory('payment/cart');
			$view->convention_key = $convention->getPublicKey();
			$view->use_passes = $convention->usePasses();
			$view->items = Model_Sale_Item::shoppingCart($convention, $user);
			$view->total_cost = Model_Sale::computeTotal($view->items);
			$view->purchases = Model_Purchase::shoppingCart($convention, $user);
			$view->total_purchases = Model_Sale::computeTotal($view->purchases);
			$view->total = $view->total_cost + $view->total_purchases;
			$view->baseurl = URL::base();
			return $this->response->body($view->render());
		} catch (Api_Exception_Unauthorized $e) {
			return $this->redirect('/auth/select?redirect-url=' . urlencode(URL::base(). $this->request->uri() . URL::query()));
		}
	}
	
	/**
	 * Helper method for dummy cart
	 */
	public function action_ok() {
		$view = Twig::factory('payment/cart-ok');
		return $this->response->body($view->render());
	}
	
	/**
	 * Helper method for dummy cart
	 */
	public function action_fail() {
		$view = Twig::factory('payment/cart-fail');
		return $this->response->body($view->render());
	}
}
