<?php
class Core
{
	static public function import($classes)
	{
		foreach((array) $classes as $class) {
			$folder = (strrpos($class, '.plugin') === false) ?
				'webcore/' : 'plugins/';

			$file = $folder.$class.'.php';

			if(is_file($file)) {
				require_once($file);
			} else {
				throw new Exception('Class not found');
			}
		}
	}

}
?>
