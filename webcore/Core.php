<?php
class Core
{
	static public function import()
	{
		$classes = func_get_args();

		foreach((array) $classes as $class) {
			if (strrpos($class, '.plugin') !== false) {
				$folder = 'plugins/';

				if (!is_file($folder.$class.'.php')) {
					$folder = 'app/plugins/';
				}

			} else if (strrpos($class, '.controller') !== false) {
				$folder = 'app/controllers/';
				$class = str_replace('.controller', '', $class);

			} else if (strrpos($class, '.model') !== false) {
				$folder = 'app/models/';
				$class = str_replace('.model', '', $class);

			} else {
				$folder = 'webcore/';
			}

			$file = $folder.$class.'.php';

			if(is_file($file)) {
				require_once($file);
			} else {
				throw new Exception("Class '$file' not found");
			}
		}
	}
}
?>
