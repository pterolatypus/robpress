<?php

  class RegistrationHelper {

    /** Construct a new Registration helper */
    public function __construct($controller) {
      $this->controller = $controller;
    }

    //Validate a list of inputs
    //Syntax:
    //    check(array(type=>value [, type=>value]*));
    //Where 'type' denotes the check to perform
    public function check($params) {
      $allowed = true;

      //Check each parameter according to what it is
      foreach ($params as $type=>$param) {
        $type = 'check_' . $type;
        $allowed = $this->$type($param);
      }

      return $allowed;
    }

    //-------------------------------------------------------------------------
    //Functions below this point are for internal use, use check instead
    //-------------------------------------------------------------------------

    //Combo checks for single attributes

    function check_username($param) {
      $allowed = true;

      $allowed = $this->check_username_valid($param) && $allowed;
      //Only perform the database check if the name is actually valid
      $allowed = $allowed && $this->check_username_available($param, -1);

      return $allowed;
    }

    function check_displayname($param) {
      $allowed = true;

      $allowed = $this->check_displayname_valid($param) && $allowed;

      return $allowed;
    }

    function check_username_noncollide($param) {
      $allowed = true;

      $allowed = $this->check_username_valid($param[0]) && $allowed;
      //Only perform the database check if the name is actually valid
      $allowed = $allowed && $this->check_username_available($param[0], $param[1]);

      return $allowed;
    }

    function check_email($param) {
      $allowed = true;

      $allowed = $this->check_email_valid($param) && $allowed;

      return $allowed;
    }

    function check_password($param) {
      $allowed = true;

      $allowed = $this->check_password_valid($param) && $allowed;

      return $allowed;
    }

    function check_password_pair($param) {
      $allowed = true;

      $allowed = $this->check_password_valid($param[0]) && $allowed;
      $allowed = $this->check_passwords_match($param) && $allowed;

      return $allowed;
    }

    //Single checks for validation

    function check_username_valid($username) {
      if(!preg_match('/^[A-Za-z][A-Za-z0-9\-_]{0,62}$/', $username)) {
        StatusMessage::add('Usernames must be between 1 and 63 characters, may only contain letters (a-z, A-Z) digits (0-9) hyphens (-) and underscores (_) and must start with a letter', 'danger');
        return false;
      }
      return true;
    }

    function check_displayname_valid($displayname) {
      if(!preg_match('/^[A-Za-z][A-Za-z0-9\-_]{0,62}$/', $displayname)) {
        StatusMessage::add('Display names must be between 1 and 63 characters, may only contain letters (a-z, A-Z) digits (0-9) hyphens (-) and underscores (_) and must start with a letter', 'danger');
        return false;
      }
      return true;
    }

    function check_email_valid($email) {
      if(!preg_match('/^(?=[A-Za-z0-9][A-Za-z0-9@._%+-]{5,253}+$)[A-Za-z0-9._%+-]{1,64}+@(?:(?=[A-Za-z0-9-]{1,63}+\.)[A-Za-z0-9]++(?:-[A-Za-z0-9]++)*+\.){1,8}+[A-Za-z]{2,63}+$/', $email)) {
        StatusMessage::add('You must enter a valid email address', 'danger');
        return false;
      }
      return true;
    }

    function check_password_valid($password) {
      if(!preg_match('/^[A-Za-z._%!$&*+-]{6,62}$/', $password)) {
        StatusMessage::add('Passwords must be between 6 and 63 characters and may only contain letters (a-z, A-Z), digits (0-9) and certain special characters (._%+-!$&*)', 'danger');
        return false;
      }
      return true;
    }

    function check_passwords_match($password_pair) {
      if($password_pair[0] != $password_pair[1]) {
				StatusMessage::add('Passwords must match','danger');
			 return false;
			}
      return true;
    }

    //ID parameter allows it to ignore a user
    //for admins editing users without changing their username
    //(otherwise it detects that the username is already in use)
    function check_username_available($username, $id) {
      $check = $this->controller->Model->Users->fetch(array('username' => $username));
      if(!empty($check) && ($id<0 || $check['id'] != $id)) {
        StatusMessage::add('Sorry, that username is already taken','danger');
        return false;
      }
      return true;
    }

  }

?>
