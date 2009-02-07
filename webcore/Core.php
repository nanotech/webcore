<?php
class Core
{
	static public $plugin_types = array('plugin', 'controller', 'model');

	static public function import()
	{
		$classes = func_get_args();

		foreach((array) $classes as $class) {
			$file = Core::plugin_file($class);
			require_once $file;
		}
	}

	static public function plugin_type($class)
	{
		$type_pos = strrpos($class, '.');
		$name = substr($class, 0, $type_pos - strlen($class));
		$type = substr($class, $type_pos + 1);

		if ($type != 'core' && !in_array($type, Core::$plugin_types)) {
			$name = $class;
			$type = NULL;
		}

		return array($name, $type);
	}

	static public function plugin_file($class)
	{
		list($plugin_name, $plugin_type) = Core::plugin_type($class);
		$file = $plugin_name.'.php';

		$search_path = array();

		switch ($plugin_type) {
		case 'core':
			$search_path[] = 'webcore/';
			break;

		case NULL:
			foreach(Core::$plugin_types as $type) {
				$search_path[] = "app/{$type}s/";
			}

			$search_path[] = 'webcore/default/plugins/';
			$search_path[] = 'webcore/';
			var_dump($search_path);
			break;

		default:
			$search_path[] = "app/{$plugin_type}s/";
			$search_path[] = "webcore/default/{$plugin_type}s/";
		}

		foreach($search_path as $path) {
			if (file_exists($path.$file)) {
				$folder = $path;
				break;
			}
		}

		if (!isset($folder)) {
			throw new MissingClass($class);
		}

		return $folder.$file;
	}
}

class MissingClass extends Exception {}

function __autoload($class)
{
	Core::import($class);
}
?>
