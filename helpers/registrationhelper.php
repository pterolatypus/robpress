<?php

  class RegistrationHelper {
    public function check($username,$displayname,$email,$password,$password2) {

      if(!preg_match('/^[A-Za-z]/', $username)) {
        StatusMessage::add('Usernames may only contain letters (a-z,A-Z) digits (0-9) hyphens (-) and underscores (_) and must start with a letter', 'danger')
      }

			$check = $this->Model->Users->fetch(array('username' => $username));
			if(!empty($check)) {
				StatusMessage::add('User already exists','danger');
				return false;
			}

			if($password != $password2) {
				StatusMessage::add('Passwords must match','danger');
				return false;
			}

      return true;
    }
  }

?>
