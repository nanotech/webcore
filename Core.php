<?php
class Core
{
	const PLUGIN_EXT = 'php';
	static public $plugin_types = array('plugin', 'controller', 'model');

	static public function import()
	{
		$classes = func_get_args();

		foreach((array) $classes as $class) {
			if (!class_exists($class)) {
				$file = Core::plugin_file($class);
				require_once $file;
			}
		}
	}

	static public function plugin_type($class)
	{
		$type_pos = strrpos($class, '.');

		if ($type_pos === false) {

			# PluginType
			$split = preg_split('/([A-Z][^A-Z]+)/', $class, 2, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

			if (count($split) >= 2) {
				$type = strtolower(array_pop($split));
				$name = implode('', $split);
			} else { $type = NULL; } # Unknown plugin

		} else {
			# Plugin.type
			$name = substr($class, 0, $type_pos - strlen($class));
			$type = substr($class, $type_pos + 1);
		}

		# Unknown plugin
		if ($type != 'core' && !in_array($type, Core::$plugin_types)) {
			$name = $class;
			$type = NULL;
		}

		return array($name, $type);
	}

	static public function plugin_file($class)
	{
		list($plugin_name, $plugin_type) = Core::plugin_type($class);
		$file = $plugin_name.'.'.self::PLUGIN_EXT;

		$search_path = array();

		switch ($plugin_type) {
		case 'core': // Built-in classes.
			$search_path[] = WEBCORE_DIR;
			break;

		case NULL: // Unknown plugin type, search all folders.
			foreach(Core::$plugin_types as $type) {
				$search_path[] = APP_DIR."/{$type}s/";
			}

			$search_path[] = WEBCORE_DIR.'/default/plugins/';
			$search_path[] = WEBCORE_DIR;
			break;

		default:
			// We know what kind of plugin it is, search the relevant folders.
			$search_path[] = APP_DIR."/{$plugin_type}s/";
			$search_path[] = WEBCORE_DIR."/default/{$plugin_type}s/";
		}

		$file = self::find_resource($file, $search_path);

		if (!isset($file)) {
			throw new MissingClass($class);
		}

		return $file;
	}

	static public function find_resource($file, $paths, $extension=false)
	{
		if ($extension) {
			$file .= '.'.$extension;
		}

		foreach((array) $paths as $folder) {
			if (substr($folder, -1, 1) != '/') {
				$folder .= '/';
			}

			if (file_exists($folder.$file)) {
				return $folder.$file;
			}
		}

		throw new MissingFile($file, $paths);
	}
}

class MissingFile extends Exception {
	protected $missing_file;
	protected $searched_paths;

	function __construct($file, $searched_paths)
	{
		$this->missing_file = $file;
		$this->searched_paths = (array) $searched_paths;

		parent::__construct();
	}

	function __toString()
	{
		$searched_paths = implode("\n", $this->searched_paths);

		return "MissingFile: Searched in $searched_paths for \"$this->missing_file\"";
	}
}
class MissingClass extends MissingFile {}

function __autoload($class)
{
	Core::import($class);
}
?>
