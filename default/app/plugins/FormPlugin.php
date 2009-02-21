<?php
class FormPlugin
{
	public $messages;

	public function form() {
		if(isset($_SESSION['messages']))
			$this->messages = $_SESSION['messages'];
	}

	public function set($text, $id = null) {
		if(is_null($id))
			$this->messages[] = $text;
		else
			$this->messages[$id] = $text;

		$this->save();
	}

	public function clear($id = null) {
		if(is_null($id))
			$this->messages = array();
		else
			unset($this->messages[$id]);

		$this->save();
	}

	private function save() {
		$_SESSION['messages'] = $this->messages;
	}
}
?>
