<?php
class Core
{
	static public $resources = array();

	static public function import($class)
	{
		if (!class_exists($class)) {
			$file = Core::find_resource($class);
			require_once $file;
		}
	}

	static public function find_resource($name, $type='php')
	{
		if (isset(self::$resources[$type][$name])) {
			return self::$resources[$type][$name];
		} else {
			throw new MissingResource($name);
		}
	}

	static public function index_resources($types)
	{
		$resource_cache = CACHE_DIR.'/resource_cache.php';

		if (file_exists($resource_cache)) {
			self::$resources = require $resource_cache;
			return;
		}

		foreach ($types as $type => $dirs) {
			foreach ($dirs as $dir) {
				if (!file_exists($dir)) {
					continue;
				}

				$resource = &self::$resources[$type];

				if (!isset($resource)) $resource = array();
				$resource = array_merge($resource, self::resources_in($dir));
			}
		}

		$exported = '<?php return '.var_export(self::$resources, true).';?>';
		file_put_contents($resource_cache, $exported);
	}

	static private function resources_in($dir, $prefix=false)
	{
		$d = dir($dir);
		$files = array();

		while (($file = $d->read()) !== false) {
			$realfile = $dir.'/'.$file;

			# Only index non-dotfiles
			$dotpos = strrpos($file, '.');

			if ($dotpos === false && $dir != WEBCORE_DIR && is_dir($realfile)) {
				$foo = self::resources_in($realfile, $file.'/');
				$files = array_merge($files, $foo);
			}

			$id = substr($file, 0, $dotpos - strlen($file));
			if (empty($id) || $id{0} == '.') {
				continue;
			}

			if ($prefix) $id = $prefix.$id;
			$files[$id] = $realfile;
		}

		$d->close();
		return $files;
	}
}

class MissingResource extends Exception {}

function __autoload($class)
{
	Core::import($class);
}
?>
