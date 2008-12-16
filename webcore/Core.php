<?php
class Core
{
	static public function import($classes)
	{
		foreach((array) $classes as $class) {
			try {
				$folder = (strrpos($class, '.plugin') === false) ?
					'webcore/' : 'plugins/';

				$file = $folder.$class.'.php';

				if(is_file($file)) {
					require_once($file);
				} else {
					throw new Exception('Class not found');
				}

			} catch (Exception $e) {
				echo $class." not found!<br />\n";
			}

		}
	}
}

# import View (Display), Controller, and Router (Director).
# The Model (Database) will be imported by the programmer later.
Core::import(array('Director', 'Display', 'Controller'));
?>
