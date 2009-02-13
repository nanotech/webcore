<?php
class IndentationParser {
	public $lines;
	public $indent;

	function IndentationParser($data)
	{
		$this->lines = explode("\n", $data);
		$this->indent = self::first_indent($data);
	}

	# Get the size and string of the first indent.
	static public function first_indent($data)
	{
		$indent_regexp = "/\n([ \t]+)/";

		preg_match($indent_regexp, $data, $matches);

		if (isset($matches[0])) {
			$indent['string'] = $matches[1];
			$indent['size'] = strlen($matches[1]);
		}

		return $indent;
	}

	public function indent_of($line)
	{
		$indent = strspn($line, $this->indent['string']);
		if ($indent != 0) $indent /= $this->indent['size'];
		return $indent;
	}

	# Convert an indented string into a tree.
	public function parse($lines)
	{
		if (is_string($lines)) {
			$lines = explode("\n", $lines);
		}

		if (empty($lines)) {
			return array();
		}

		#echo "=BEGIN=\n";

		$tree = array();
		$branch = array();
		$element = trim($lines[0]);
		$last_element = $element;
		#echo '==========opened '.$element."\n\n";
		$min_indent = $this->indent_of($lines[0]);

		foreach($lines as $number => $line) {
			if (trim($line) == '') continue;
			$indent = $this->indent_of($line);

			if ($indent > $min_indent) {
				$branch[] = $line;

				#echo $line."\n";
			} elseif ($indent == $min_indent && $number != 0) {
				#echo '==========closed '.$element."\n\n";
				$tree[] = $this->close($element, $branch);

				$element = trim($line);
				$branch = array();
				#echo '==========opened '.$element."\n\n";
			}
		}

		#echo '==========closed '.$element."\n\n";
		$tree[] = $this->close($element, $branch);

		#echo "=END=\n\n";

		return $tree;
	}

	# Create an element node.
	protected function close($element, $branch)
	{
		return array(
			'element' => $element,
			'children' => $this->parse($branch)
		);
	}

	# Prints a formatted tree, the inverse of parse.
	public static function inspect($tree, $indent_string='  ', $depth=0)
	{
		if (is_array($tree)) {
			foreach($tree as $branch) {
				$indent = str_repeat($indent_string, $depth);
				echo $indent.$branch['element']."\n";
				self::inspect($branch['children'], $indent_string, $depth+1);
			}
		}
	}
}
?>
