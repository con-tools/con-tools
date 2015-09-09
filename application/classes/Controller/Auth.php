<?php

class Controller_Auth extends Controller {

  public function action_verify() {
    $data = json_decode($this->request->body());
    if ($data['token'] && $this->is_valid($data['token']))
      $this->send([ "status" => true ]);
    else
      $this->send([ "status" => false ]);
  }
  
  public function action_start() {
    $data = json_decode($this->request->body()) ?: [];
    $this->send([ "auth-url" => Auth::getProvider(@$data['provider'] ?: 'google')->getAuthenticationURL() ]);
  }
  
  private function is_valid($token) {
    return false;
  }

}
