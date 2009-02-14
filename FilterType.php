<?php
/**
 * FilterType allows static type checking on Filters,
 * like functional languages such as Haskell.
 */
class FilterType {
	public $in;
	public $out;

	public function __construct($in, $out)
	{
		$this->in = $in;
		$this->out = $out;
	}

	static public function parse($string)
	{
		$args = explode(' -> ', $string);
		return new FilterType($args[0], $args[1]);
	}

	public function check(FilterType $other)
	{
		return $this->out != $other->in && 
			!$this->is_polymorphic('out') &&
			!$other->is_polymorphic('in');
	}

	public function is_polymorphic($put)
	{
		if ($put != 'in' && $put != 'out') {
			throw new InvalidArgumentException('Argument must be "in" or "out".');
		}

		return (strlen($this->$put) == 1 && ctype_alpha($this->$put));
	}

	public function __toString()
	{
		return $this->in.' -> '.$this->out;
	}
}
?>
