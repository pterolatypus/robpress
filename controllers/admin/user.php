<?php

namespace Admin;

class User extends AdminController {

	public function index($f3) {
		$users = $this->Model->Users->fetchAll();
		$f3->set('users',$users);
	}

	public function edit($f3) {
		$id = $f3->get('PARAMS.3');
		$user = $this->Model->Users->fetch($id);
		if($this->request->is('post')) {

			extract($this->request->data);

			//Check that the new information is valid (the rules apply to admins, too)
			if ($this->Registration->check(array('username_noncollide'=>array($username, $id), 'displayname'=>$displayname)) && (empty($password) || $this->Registration->check(array('password'=>$password)))) {

				//If valid, save the updated details into the database
				$oldpass = $user->password;
				$user->copyfrom('POST');
				if(empty($user->password)) {
					$user->setPassword($oldpass);
				} else {
					$user->setPassword($user->password);
				}

				$user->save();
				\StatusMessage::add('User updated succesfully','success');

				//If it succeeds, reroute to index
				return $f3->reroute('/admin/user');
			}
		}
		$_POST = $user->cast();
		$f3->set('u',$user);
		$f3->set('formhelper',$this->Form);
	}

	public function delete($f3) {
		$id = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetch($id);

		if($id == $this->Auth->user('id')) {
			\StatusMessage::add('You cannot remove yourself','danger');
			return $f3->reroute('/admin/user');
		}

		//Remove all posts and comments
		$posts = $this->Model->Posts->fetchAll(array('user_id' => $id));
		foreach($posts as $post) {
			$post_categories = $this->Model->Post_Categories->fetchAll(array('post_id' => $post->id));
			foreach($post_categories as $cat) {
				$cat->erase();
			}
			$post->erase();
		}
		$comments = $this->Model->Comments->fetchAll(array('user_id' => $id));
		foreach($comments as $comment) {
			$comment->erase();
		}
		$u->erase();

		\StatusMessage::add('User has been removed','success');
		return $f3->reroute('/admin/user');
	}


}

?>
