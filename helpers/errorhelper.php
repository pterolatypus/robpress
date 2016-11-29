<?php

  class ErrorHelper {

    /** Construct a new Error helper */
    public function __construct($controller) {
      $this->controller = $controller;
    }

    public function error($code='500') {
      $f3 = $this->controller->f3;
      $debug = $this->controller->Model->Settings->getSetting('debug');
      $f3->set('debug', $debug);
      return $f3->error($code);
    }

    public function notfound() {
      return $this->error('404');
    }

  }

?>
