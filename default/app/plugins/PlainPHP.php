<?php
/**
 * Simple, PHP eval-ing filter, with a parse_file optimization using require.
 */
class PlainPHP extends Filter {

	public function parse_file($_file) {
		extract($this->meta, EXTR_PREFIX_SAME, 'x');
		ob_start();
		require $_file;
		return ob_get_clean();
	}

	public function parse($_data)
	{
		// Bring meta variables into the local scope.
		extract($this->meta, EXTR_PREFIX_SAME, 'x');

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
