<?php

  class FormHelper {

    /** Construct a new Registration helper */
    public function __construct($controller) {
      $this->controller = $controller;
    }

    public function getForm() {
      return new Form($this->controller->XSS);
    }

  }

?>
