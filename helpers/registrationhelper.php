<?php

  class RegistrationHelper {

    /** Construct a new Regsitration helper */
    public function __construct($controller) {
      $this->controller = $controller;
    }

    public function check($username,$displayname,$email,$password,$password2) {

      $allowed = true;


      if(!preg_match('/^[A-Za-z][A-Za-z0-9\-_]{0,62}$/', $username)) {
        StatusMessage::add('Usernames must be between 1 and 63 characters, may only contain letters (a-z, A-Z) digits (0-9) hyphens (-) and underscores (_) and must start with a letter', 'danger');
        $allowed = false;
      }

      if(!preg_match('/^[A-Za-z][A-Za-z0-9\-_]{0,62}$/', $displayname)) {
        StatusMessage::add('Display names must be between 1 and 63 characters, may only contain letters (a-z, A-Z) digits (0-9) hyphens (-) and underscores (_) and must start with a letter', 'danger');
        $allowed = false;
      }

      if(!preg_match('/^(?=[A-Za-z0-9][A-Za-z0-9@._%+-]{5,253}+$)[A-Za-z0-9._%+-]{1,64}+@(?:(?=[A-Za-z0-9-]{1,63}+\.)[A-Za-z0-9]++(?:-[A-Za-z0-9]++)*+\.){1,8}+[A-Za-z]{2,63}+$/', $email)) {
        StatusMessage::add('You must enter a valid email address', 'danger');
        $allowed = false;
      }

      if(!preg_match('/^[A-Za-z._%!$&*+-]{6,62}$/', $password)) {
        StatusMessage::add('Passwords must be between 6 and 63 characters and may only contain letters (a-z, A-Z), digits (0-9) and certain special characters (._%+-!$&*)', 'danger');
        $allowed = false;
      }

			if($password != $password2) {
				StatusMessage::add('Passwords must match','danger');
				$allowed = false;
			}
      
			$check = $this->controller->Model->Users->fetch(array('username' => $username));
			if(!empty($check)) {
				StatusMessage::add('Sorry, that username is already taken','danger');
				$allowed = false;
			}

      return $allowed;
    }

  }

?>
