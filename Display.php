<?php
/**
 * Display manages an array of Filters and metadata
 * (exported template variables), and renders files.
 */
class Display
{
	public $filters;
	public $meta;

	public function __construct($filters=array(), $meta=array())
	{
		$this->filters = (array) $filters;
		$this->meta    = (array) $meta;
	}

	/**
	 * Apply the Display's filters to a file.
	 */
	public function render($resource_name, $group='views')
	{
		global $config;

		$dotpos = strrpos($resource_name, '.');

		if ($dotpos !== false) {
			$type = substr($resource_name, $dotpos + 1);
			$resource_name = substr($resource_name, 0, $dotpos);
		} else {
			$type = false;
		}

		# Find the actual file.
		$resource = Core::find_resource($resource_name, $group, $type);

		# Load the inital data.
		list($data, $meta) = $resource->parse($this->meta);

		# Apply filters, if any.

		foreach($this->filters as $filter) {
			list($data, $meta) = self::apply_filter($filter, $data, $meta);
		}

		return $data;
	}

	public static function read_filter_entry($filter)
	{
		if (is_array($filter)) {
			$args = $filter;
			$name = array_shift($args);
		} else {
			$name = $filter;
			$args = array();
		}

		return array($name, $args);
	}

	public static function apply_parser($parsers, $file, $meta=array())
	{
		$data = false;

		foreach ((array) $parsers as $parser) {
			if (class_exists($parser))
			{
				if ($data) {
					$parse_file = false;
				} else {
					$parse_file = method_exists($parser, 'parse_file');
					$data = ($parse_file === true) ? $file : file_get_contents($file);
				}
				list($data, $meta) = self::apply_filter($parser, $data, $meta, $parse_file);
			}
			else if (function_exists($parser))
			{
				$data = $parser(file_get_contents($file));
			}
			else
			{
				exit('Resource '.$file.' does not have a parser!'); // TODO FIXME very ugly.
			}
		}

		return array($data, $meta);
	}

	public static function apply_filter($filter, $data, $meta, $parse_file=false)
	{
		# Array entries are Filters with arguments.
		list($filter_name, $filter_args) = self::read_filter_entry($filter);

		# Add the data to the filter arguments.
		array_unshift($filter_args, $data);

		if (class_exists($filter_name))
		{
			# Initialize filter.
			$filter = new $filter_name($meta);

			# Run the filter.
			$method = ($parse_file) ? 'parse_file' : 'parse';
			$parsed_data = call_user_func_array(array($filter, $method), $filter_args);
			$meta = $filter->meta;
		}
		else if (function_exists($filter_name))
		{
			$parsed_data = $filter_name($data);
		}

		return array($parsed_data, $meta);
	}
}
?>
