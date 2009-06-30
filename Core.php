<?php
class Core
{
	const CODE_GROUP      = 'code';
	const DEFAULT_OUTPUT  = 0;

	static public $resources = array();

	static public function import()
	{
		$classes = func_get_args();

		foreach ($classes as $class) {
			self::import_one($class);
		}
	}

	static public function import_one($class)
	{
		if (!class_exists($class)) {
			$file = Core::find_resource($class)->file;
			require_once $file;
		}
	}

	static public function find_resource($name, $group=self::CODE_GROUP, $type=false)
	{
		$resource = &self::$resources[$group][$name];

		if (isset($resource)) {
			if ($type === false) {
				if (isset($resource[self::DEFAULT_OUTPUT])) {
					$typed_resource = $resource[self::DEFAULT_OUTPUT];
				} else {
					$typed_resource = reset($resource);
				}

			} else {
				$typed_resource = $resource[$type];
			}

			return $typed_resource;
		}

		throw new MissingResource($name);
	}

	static public function all_resources_in($group)
	{
		return self::$resources[$group];
	}

	static public function index_resources($groups)
	{
		global $config;

		$cache_resources = $config['cache']['resources'];

		if ($cache_resources) {
			$resource_cache = $config['cache']['directory'].'/resource_cache.php_serial';

			if (file_exists($resource_cache)) {
				self::$resources = unserialize(file_get_contents($resource_cache));
				return;
			}
		}

		foreach ($groups as $group => $dirs) {
			foreach ($dirs as $dir) {
				if (!file_exists($dir)) {
					continue;
				}

				$resource = &self::$resources[$group];

				if (!isset($resource)) $resource = array();
				$resource = array_merge($resource, self::find_resources_in($dir));
			}
		}

		if ($cache_resources) {
			$exported = serialize(self::$resources);
			file_put_contents($resource_cache, $exported);
		}
	}

	static private function find_resources_in($dir, $prefix=false)
	{
		$d = dir($dir);
		$files = array();

		while (($file = $d->read()) !== false) {
			$realfile = $dir.'/'.$file;

			# Get the file parts.
			$parts = explode('.', strrev($file), 3);
			$parts = array_map('strrev', $parts);
			$part_count = count($parts);

			# Recurse into folders.
			if ($part_count === 1 && $dir != WEBCORE_DIR && is_dir($realfile)) {
				$contents = self::find_resources_in($realfile, $file.'/');
				$files = array_merge($files, $contents);
				continue;
			}

			# Name parts.
			$id = end($parts);
			$format = $parts[0];
			$output = ($part_count >= 3) ? $parts[1] : self::DEFAULT_OUTPUT;

			# Don't index dotfiles
			if (empty($id) || $id{0} == '.') {
				continue;
			}

			# Apply a prefix, if one exists.
			if ($prefix) $id = $prefix.$id;

			$files[$id][$output] = new Resource($realfile, $format);
		}

		$d->close();
		return $files;
	}

	static public function reset()
	{
		self::$resources = array();
	}
}

class Resource {
	public $file;
	public $format;

	public function __construct($file, $format)
	{
		$this->file = $file;
		$this->format = $format;
	}

	public function __toString()
	{
		list($parsed,) = $this->parse();
		return $parsed;
	}

	public function parse($meta=array())
	{
		global $config;
		$parser = $config['parsers'][$this->format];
		return Display::apply_parser($parser, $this->file, $meta);
	}
}

class MissingResource extends Exception {}

function __autoload($class)
{
	Core::import_one($class);
}
?>
