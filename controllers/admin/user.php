<?php

namespace Admin;

class User extends AdminController {

	public function index($f3) {
		$users = $this->Model->Users->fetchAll();
		$f3->set('users',$users);
	}

	public function edit($f3) {
		$id = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetch($id);
		if($this->request->is('post')) {

			extract($this->request->data);

			//Check that the new information is valid (the rules apply to admins, too)
			if ($this->Registration->check(array('username_noncollide'=>array($username, $id), 'displayname'=>$displayname,'password'=>$password))) {

				//If valid, save the updated details into the database
				$u->copyfrom('POST');
				$u->setPassword($this->request->data['password']);
				$u->save();
				\StatusMessage::add('User updated succesfully','success');

				//If it succeeds, reroute to index
				return $f3->reroute('/admin/user');
			} else {
				//If it fails, stay on the same page
				//(the checking method handles status messages, so we don't have to)
				return $f3->reroute($f3->get('url'));
			}
		}
		$_POST = $u->cast();
		$f3->set('u',$u);
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
