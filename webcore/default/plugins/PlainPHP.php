<?php
/**
 * Simple, PHP eval-ing filter.
 */
class PlainPHP extends Filter {
	static public $type = 'php -> a';

	public function parse($_data)
	{
		// Bring meta variables into the local scope.
		foreach($this->meta as $_key => $_value) {
			${$_key} = $_value;
		}

		// Close the implicit PHP block so we can
		// eval the string like a normal PHP file.
		$_data = '?>'.$_data;

		// Buffer, eval, get, and return the data.
		ob_start();
		eval($_data);
		return ob_get_clean();
	}
}
?>
