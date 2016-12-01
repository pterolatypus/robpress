<?php

class CSRFHelper {

  public function __construct($controller) {
    $this->controller = $controller;
  }

  public function warn() {
    StatusMessage::add("WARNING: CSRF ATTEMPT DETECTED", 'danger');
    return $this->controller->f3->reroute('/');
  }

  public function validate($token) {
    if(isset($debug)) {
      return true;
    }
    if(empty($token) || $token != $this->controller->f3->get('SESSION.CSRF')) {
      return $this->warn();
    }
  }

}

?>
