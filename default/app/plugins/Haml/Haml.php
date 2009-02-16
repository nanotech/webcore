<?php
require 'IndentationParser.php';

class HamlPlugin extends Filter {

	public $template_dir;
	public $template_extension;

	public $special_chars;
	public $doctypes;
	public $html;

	protected $xml;
	protected $regex;

	static public $type = 'haml -> php';

	public function __construct($_opts)
	{
		parent::__construct($_opts);

		$this->special_chars = '!%#.';

		$this->template_dir = 'views';
		$this->template_extension = '.haml';

		$this->encoding = 'utf-8';

		$this->xml = new XMLWriter(); 
		$this->xml->openMemory();
		$this->xml->setIndent(true); 

		$this->doctypes = array(
			'XML' =>
				'<?xml version="1.0" encoding="%s"?'.'>',
			'Strict' =>
				array('html', '-//W3C//DTD XHTML 1.0 Strict//EN', 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'),
			'Transitional' =>
				array('html', '-//W3C//DTD XHTML 1.0 Transitional//EN', 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'),
			'Frameset' =>
				array('html', '-//W3C//DTD XHTML 1.0 Frameset//EN', 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd'),
			'1.1' =>
				array('html', '-//W3C//DTD XHTML 1.1//EN', 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'),
			'HTML Strict' =>
				array('HTML', '-//W3C//DTD HTML 4.01//EN', 'http://www.w3.org/TR/TR/html4/strict.dtd'),
			'HTML Transitional' =>
				array('HTML', '-//W3C//DTD HTML 4.01 Transitional//EN', 'http://www.w3.org/TR/TR/html4/loose.dtd'),
			'HTML Frameset' =>
				array('HTML', '-//W3C//DTD HTML 4.01 Frameset//EN', 'http://www.w3.org/TR/TR/html4/frameset.dtd'),
		);


		$regex['argument'] = '(\{[^\}]+\})?';
		$regex['tag'] = '[a-z0-9\-_!]+';
		$regex['haml'] = "/(([!%#\.]{$regex['tag']})+){$regex['argument']}( .*)?/i";
		$regex['head'] = "/([%#\.]{$regex['tag']}|!!!)([#\.]{$regex['tag']})*/i";
		$regex['attr'] = '/(:([a-z]+)|[\'"](([a-z]+:?)+)[\'"])+ +?=> +?[\'"]([^\'"]+)[\'"]/';

		$this->regex = $regex;
	}

	public function parse($data)
	{
		$lines = explode("\n", $data);

		$indent_parser = new IndentationParser($data);
		$this->indent_parser = &$indent_parser;
		$tree = $indent_parser->parse($data);

		$this->xml->setIndentString($indent_parser->indent['string']); 

		$html_tree = $this->parse_haml($tree);
		$this->render_xml($html_tree);

		return $this->xml->flush(); 
	}

	protected function parse_haml($tree)
	{
		$regex = $this->regex;

		foreach($tree as &$branch) {
			$line = $branch['element'];
			$element = &$branch['element'];
			$element = array();

			# Split the line
			preg_match($regex['haml'], $line, $tag_parts);

			if (!empty($tag_parts)) {
				# Text
				if(isset($tag_parts[4])) $text = trim($tag_parts[4]);
				else $text = null;

				# Tags, IDs, and Classes
				if (isset($tag_parts[2])) {
					preg_match($regex['head'], $tag_parts[2], $head_tags);

					$action = $head_tags[1]{0};
					$tag = substr($head_tags[1], 1);

					unset($head_tags[0], $head_tags[1]);
				}

				# Attributes
				$attributes = array();

				if (isset($tag_parts[3])) {
					preg_match_all($regex['attr'], $tag_parts[3], $attr_matches);

					$attribute_count = count($attr_matches[0]);
					for($i=0;$i<$attribute_count;++$i) {
						$name = $attr_matches[2][$i];
						if (empty($name)) $name = $attr_matches[3][$i];
						$attributes[$name] = $attr_matches[5][$i];
					}
				}

				if(isset($action) && strpos($this->special_chars, $action) !== false) {
					switch($action) {
					case '#':
						$attributes['id'] = $tag;
						$tag = 'div';
						break;
					case '.':
						$attributes['class'] = $tag;
						$tag = 'div';
						break;
					}

					foreach ($head_tags as $head_tag) {
						$a = $head_tag{0};
						$t = substr($head_tag, 1);

						switch($a) {
						case '#':
							$attributes['id'] = $t;
							break;
						case '.':
							if (!isset($attributes['class']) || empty($attributes['class'])) {
								$attributes['class'] = $t;
							} else {
								$attributes['class'] .= ' '.$t;
							}
							break;
						}
					}

				} else {
					$action = 'text';
					$tag = null;
					$text = $line;
				}

				switch($action) {
				case '!':
					$type = trim(substr($line, 4));
					if (empty($type)) $type = 'Transitional';

					// If the DOCTYPE doesn't look like it's in the list,
					// remove the last word off it and try again.
					if(!array_key_exists($type, $this->doctypes)) {
						$type_words = explode(' ', $type);
						$encoding = array_pop($type_words);
						$type = implode(' ', $type_words);

						if(!array_key_exists($type, $this->doctypes)) {
							throw new Exception('Unknown DOCTYPE');
						}
					}

					$element['doctype'] = &$this->doctypes[$type];
					break;

				case 'text':
					if (isset($return[count($return)-1]['element']['text'])) {
						$return[count($return)-1]['element']['text'] .= "\n".$text;
					}

					break;

				case '#':
				case '.':
				case '%':
					$element['tag'] = $tag;

					if ($attributes) $element['attributes'] = $attributes;
					if ($text) $element['text'] = $text;

					break;
				}

			} else {
				# Literal
				$element['text'] = $line;
			}

			$children = &$branch['children'];

			if (!empty($children)) {
				$children = $this->parse_haml($children);
			}
		}
		return $tree;
	}

	protected function render_xml($tree)
	{
		$xml = &$this->xml;
		if (empty($tree)) return;

		foreach($tree as $id => $branch) {
			$element = &$branch['element'];

			if (isset($element['tag'])) {
				$xml->startElement($element['tag']); 

				if (isset($element['attributes'])) {
					foreach($element['attributes'] as $attr => $value) {
						$xml->writeAttribute($attr, $value);
					}
				}

				if (isset($element['text'])) {
					$xml->text($element['text']);
				}

				if (isset($branch['children'])) {
					$this->render_xml($branch['children']);
				}

				$xml->endElement(); 
			} else if (isset($element['text'])) {
				$text = $element['text'];
				$xml->text($text);
			} elseif (isset($element['doctype'])) {
				$dt = &$element['doctype'];
				$xml->writeDTD($dt[0], $dt[1], $dt[2]); 
			}
		}
	}

	protected function format_text($text, $depth)
	{
		$text = trim($text);
		$lines = explode("\n", $text);

		if (count($lines) > 1) {
			$indent = str_repeat($this->indent_parser->indent['string'], $depth);
			$last = substr($indent, $this->indent_parser->indent['size']);

			foreach($lines as $k => $line) {
				$lines[$k] = $indent.$line;
			}

			$lines[] = $last;

			$text = "\n".implode("\n", $lines);
		}

		return $text."\n";
	}

	public function assign()
	{
		// Nothing here yet.
	}
}
?>
