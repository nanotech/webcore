<?php
class Stack extends ArrayObject
{
	private $_array;

	public function __construct()
	{
		$args = func_get_args();

		if (isset($args[0]) && is_array($args[0])) {
			$this->_array = $args[0];
		} else {
			$this->_array = $args;
		}

		parent::__construct($this->_array);
	}

	public function each($callback)
	{
		$iterator = $this->getIterator();

		while($iterator->valid()) {
			$callback($iterator->current());
			$iterator->next();
		}

	}

	public function first() {
		return $this->_array[0];
	}

	public function last()
	{
		$last_id = $this->size()-1;
		return ($last_id == -1) ? NULL : $this->_array[$last_id];
	}

	public function pop()
	{
		$_element = array_pop($this->_array);
		parent::__construct($this->_array);
		return $_element;
	}

	public function push($value)
	{
		array_push($this->_array, $value);
		parent::__construct($this->_array);
		return $this->size();
	}

	public function size() {
		return count($this->_array);
	}
}
?>
