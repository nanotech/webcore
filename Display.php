<?php
/**
 * Display manages a list of Filters, a Stack of data,
 * a meta array, and renders files.
 */
class Display
{
	public $filters;
	public $stack;
	public $meta;

	public function __construct($filters=array())
	{
		$this->filters = $filters;
		$this->stack = new Stack;
	}

	/**
	 * Apply the Display's filters to a file.
	 */
	public function render($file)
	{
		# Find the actual file.
		$type = $this->filter_type(reset($this->filters));
		$file = Core::find_resource(
			$file, array('app/views', WEBCORE_DIR.'/default/views'),
			$type->in # extension
		);

		$type->out = $type->in;

		# Load the inital data.
		$this->stack->push(file_get_contents($file));

		foreach($this->filters as $filter_name) {

			# Filters with arguments are arrays.
			if (is_array($filter_name)) {
				$filter_args = $filter_name;
				$filter_name = array_shift($filter_args);
			} else {
				$filter_args = array();
			}

			# Initialize filter.
			$filter = new $filter_name($this->meta);

			# Get the new $in.
			$new_type = $this->filter_type($filter);

			# Type check
			if ($type->check($new_type)) {
				throw new FilterTypeError($type, $new_type);
			}

			# The new type is now old.
			$type = $new_type;

			# Pop a data item off the stack.
			$data = $this->stack->pop();

			# Add the data to the filter arguments.
			array_unshift($filter_args, $data);

			# Run the filter...
			$parsed_data = call_user_func_array(array($filter, 'parse'), $filter_args);

			# ...and put the result back on the stack.
			$this->stack->push($parsed_data);

			# Load the metadata from the Filter.
			$this->meta = $filter->meta;
		}

		return $this->stack->last();
	}

	/**
	 * Get a filter's type
	 */
	public function filter_type($filter, $which=false)
	{
		$reflection = new ReflectionClass($filter);
		$type_string = $reflection->getProperty('type')->getValue();
		return FilterType::parse($type_string);
	}
}

/**
 * Filter type checking error.
 */
class FilterTypeError extends Exception {
	protected $a;
	protected $b;

	public function __construct($a, $b)
	{
		$this->a = $a;
		$this->b = $b;

		parent::__construct();
	}

	public function __toString()
	{
		return __CLASS__.': "'.$this->a->out.'" does not match "'.$this->b->in.'"';
	}
}
?>
