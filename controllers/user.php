<?php
class User extends Controller {

	public function view($f3) {
		$userid = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetch($userid);
		if(empty($u)) {
			$this->Error->notfound();
		}

		$articles = $this->Model->Posts->fetchAll(array('user_id' => $userid));
		$comments = $this->Model->Comments->fetchAll(array('user_id' => $userid));

		$f3->set('u',$u);
		$f3->set('articles',$articles);
		$f3->set('comments',$comments);
	}

	public function add($f3) {
		$f3->set('formhelper',$this->Form);
		if($this->request->is('post')) {

			extract($this->request->data);

			if ($this->Registration->check(array('username'=>$username,'displayname'=>$displayname,'email'=>$email,'password_pair'=>array($password, $password2)))) {


				$user = $this->Model->Users;
				$user->copyfrom('POST');
				$user->created = mydate();
				$user->bio = '';
				$user->level = 1;
				//Generate random IDs until we get an unused one
				do {
					$id = mt_rand();
					$user->id = $id;
				} while (!empty($user->fetch(array('id' => $id))));
				if(empty($displayname)) {
					$user->displayname = $user->username;
				}

				//Set the users password
				$user->setPassword($password);
				$user->save();

				StatusMessage::add('Registration complete','success');
				return $f3->reroute('/user/login');
			}
		}

	}

	public function login($f3) {
		/** YOU MAY NOT CHANGE THIS FUNCTION - Make any changes in Auth->checkLogin, Auth->login and afterLogin() */
		if ($this->request->is('post')) {

			//Check for debug mode
			$settings = $this->Model->Settings;
			$debug = $settings->getSetting('debug');

			//Either allow log in with checked and approved login, or debug mode login
			list($username,$password) = array($this->request->data['username'],$this->request->data['password']);
			if (
				($this->Auth->checkLogin($username,$password,$this->request,$debug) && ($this->Auth->login($username,$password))) ||
				($debug && $this->Auth->debugLogin($username))) {

					$this->afterLogin($f3);

			} else {
				StatusMessage::add('Invalid username or password','danger');
			}
		}
		//Necessary for input sanitisation
		$f3->set('formhelper', $this->Form);
	}

	/* Handle after logging in */
	private function afterLogin($f3) {
				StatusMessage::add('Logged in succesfully','success');

				//Redirect to where they came from
				if(isset($_GET['from']) && preg_match('/^\//', $_GET['from'])) {
					$f3->reroute($_GET['from']);
				} else {
					$f3->reroute('/');
				}
	}

	public function logout($f3) {
		$this->Auth->logout();
		StatusMessage::add('Logged out succesfully','success');
		$f3->reroute('/');
	}


	public function profile($f3) {
		$id = $this->Auth->user('id');
		if(!$id) {
			$f3->reroute('/user/login?from=' . urlencode('/user/profile'));
		}
		$u = $this->Model->Users->fetch($id);
		if($this->request->is('post')) {

				extract($this->request->data);
				$oldpass = $u->password;
			if($this->Registration->check(array('displayname' => $displayname)) && (empty($password) || $this->Registration->check(array('password'=>$password)))) {
				$u->copyfrom('POST');
				if(!empty($password)) {
					$u->setPassword($password);
				} else {
					$u->setPassword($oldpass);
				}

				//Handle avatar upload
				if(isset($_FILES['avatar']) && isset($_FILES['avatar']['tmp_name']) && !empty($_FILES['avatar']['tmp_name'])) {
					$file = $_FILES['avatar'];
					if($this->Registration->check(array('avatar_file' => $file))) {
						$url = File::Upload($file);
						$u->avatar = $url;
						$fail = true;
					}
				} else if(isset($reset)) {
					$u->avatar = '';
				}

				if(isset($fail)) {
				$u->save();
				\StatusMessage::add('Profile updated succesfully','success');
				}
			}

			//return $f3->reroute('/user/profile');
		}
		$_POST = $u->cast();
		$f3->set('u',$u);
		$f3->set('formhelper',$this->Form);
	}

//FIXED - this isn't functionality, this is just a straight-up backdoor
/*
	public function promote($f3) {
		$id = $this->Auth->user('id');
		$u = $this->Model->Users->fetch($id);
		$u->level = 2;
		$u->save();
		return $f3->reroute('/');
	}
*/

}
?>
