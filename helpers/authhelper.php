<?php

	class AuthHelper {

		/** Construct a new Auth helper */
		public function __construct($controller) {
			$this->controller = $controller;
		}

		/** Attempt to resume a previously logged in session if one exists */
		public function resume() {
			$f3=Base::instance();

			//Ignore if already running session
			if($f3->exists('SESSION.user.id')) return;

			//Log user back in from cookie
			if($f3->exists('COOKIE.RobPress_User')) {
				//$user = unserialize(base64_decode($f3->get('COOKIE.RobPress_User')));
				$token = $f3->get('COOKIE.RobPress_User');

				$db = $this->controller->db;
				$results = $db->query('SELECT user FROM logins WHERE token=?', $token);
				if(empty($results)) {
					//If the cookie is invalid, delete it and do nothing
					$f3->clear('COOKIE.RobPress_User');
					return;
				}
				//Fetch the user's details to log them in
				$userid = $results[0];
				$user = $this->controller->Model->Users->fetch(array('id' => $userid));
				//Invalidate the cookie
				$db->query('DELETE FROM logins WHERE token=?', $token);
				//Log the user in (serving a fresh cookie as we do)
				$this->doLogin($user);
			}
		}

		//TODO: add brute force protection here
		/** Perform any checks before starting login */
		public function checkLogin($username,$password,$request,$debug) {

			//DO NOT check login when in debug mode
			if($debug == 1) { return true; }

			return true;
		}

		/** Look up user by username and password and log them in */
		public function login($username,$password) {
			$f3=$this->controller->f3;
			$db = $this->controller->db;


			//$results = $db->query("SELECT * FROM `users` WHERE `username`='$username' AND `password`='$password'");
			//FIXED - login now uses prepared statements to avoid SQL injection
			//FIXED - also implemented password hashing
			$results = $db->query('SELECT * FROM users WHERE username=?', $username);
			//$user = $this->controller->Model->Users->fetch(array('username' => $username));
			//If a user was found
			if (!empty($results)) {

			$user = $results[0];

				//Verify the user's password
				if(password_verify($password, $user['password'])) {
					//If passwords match, login
				return $this->doLogin($user);
				}

			}


			return false;
		}

		public function doLogin($user) {
			$this->setupSession($user);
			return $this->forceLogin($user);
		}

		/** Log user out of system */
		public function logout() {
			$f3=$this->controller->f3;

			//Kill the session
			//FIXED - use the fatfree version instead
			//session_destroy();
			$f3->clear("SESSION");

			//Kill the cookie
			//FIXED - use the fatfree version instead
			//setcookie('RobPress_User', '', time() - 42000,'/');
			$f3->clear("COOKIE.RobPress_User");
		}

		/** Set up the session for the current user */
		public function setupSession($user) {
			$f3=Base::instance();

			//Remove previous session
			$f3->clear("SESSION");
			//Setup new session
			new Session(NULL, 'CSRF');

			//Setup cookie for storing user details and for relogging in
			//Clear previous cookie
			$f3->clear("COOKIE.RobPress_User");
			//Generate new token
			$db = $this->controller->db;
			do {
				$newtoken = mt_rand();
			} while (!empty($db->query('SELECT * FROM logins WHERE token=?', $newtoken)));
			//Store token in database
			$db->query('INSERT INTO logins VALUES(?, ?)',array(1=>$newtoken, 2=>$user['id']));
			//Pass token to user
			$f3->set("COOKIE.RobPress_User", $newtoken, time()+3600*24*30,'/');

		}

		/** Not used anywhere in the code, for debugging only */
		public function specialLogin($username) {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$f3 = Base::instance();
			$user = $this->controller->Model->Users->fetch(array('username' => $username));
			$array = $user->cast();
			return $this->forceLogin($array);
		}

		/** Not used anywhere in the code, for debugging only */
		public function debugLogin($username,$password='admin') {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$user = $this->controller->Model->Users->fetch(array('username' => $username));

			//Create a new user if the user does not exist
			if(!$user) {
				$user = $this->controller->Model->Users;
				$user->username = $user->displayname = $username;
				$user->email = "$username@robpress.org";
				$user->setPassword($password);
				$user->created = mydate();
				$user->bio = '';
				$user->level = 2;
				$user->save();
			}

			//Update user password
			$user->setPassword($password);

			//Move user up to administrator
			if($user->level < 2) {
				$user->level = 2;
				$user->save();
			}

			//Log in as new user
			return $this->forceLogin($user);
		}

		/** Force a user to log in and set up their details */
		public function forceLogin($user) {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$f3=Base::instance();

			if(is_object($user)) { $user = $user->cast(); }

			$f3->set('SESSION.user',$user);
			return $user;
		}

		/** Get information about the current user */
		public function user($element=null) {
			$f3=Base::instance();
			if(!$f3->exists('SESSION.user')) { return false; }
			if(empty($element)) { return $f3->get('SESSION.user'); }
			else { return $f3->get('SESSION.user.'.$element); }
		}

	}

?>
