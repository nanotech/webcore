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
	public function render($file)
	{
		# Find the actual file.
		$file = Core::find_resource($file, 'views');

		# Load the inital data.
		$data = file_get_contents($file);
		$meta = $this->meta;

		foreach($this->filters as $filter) {
			list($data, $meta) = $this->apply_filter($filter, $data, $meta);
		}

		return $data;
	}

	public static function apply_filter($filter_name, $data, $meta)
	{
		# Array entries are Filters with arguments.
		if (is_array($filter_name)) {
			$filter_args = $filter_name;
			$filter_name = array_shift($filter_args);
		} else {
			$filter_args = array();
		}

		# Initialize filter.
		$filter = new $filter_name($meta);

		# Add the data to the filter arguments.
		array_unshift($filter_args, $data);

		# Run the filter.
		$parsed_data = call_user_func_array(array($filter, 'parse'), $filter_args);

		return array($parsed_data, $filter->meta);
	}
}
?>
