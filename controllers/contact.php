<?php

class Contact extends Controller {

	public function index($f3) {
		if($this->request->is('post')) {
			extract($this->request->data);
			$from = "From: $from";


			$to = $site['email'];
			mail($to,$subject,$message,$from);

			StatusMessage::add('Thank you for contacting us');
			return $f3->reroute('/');
		}
		$f3->set('formhelper',$this->Form);
	}

}

?>
