<?php
class DefaultController extends Controller
{
	public function index() {
		$this->render('index');
	}

	public function error() {
		$this->render('error');
	}
}
?>
