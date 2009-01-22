<?php
/**
 *  Phammable HAML interpreter for PHP (http://phammable.org/)
 *  Copyright © 2008 James Yopp (james at jyopp dot com)
 *
 *  Additions and modifications by NanoTech (http://nanotech.nanotechcorp.net)
 *  
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *  
 *  
 *  Revision History:
 *  git, 16 December 2008:
 *  	- Added to the WebCore git repo. Use diff to see changes.
 *  v 0.8, 22 June 2008:
 *  	- First public release. Added doctypes for various XHTML specifications.
 *
 *  INSTALLING / USING This File:
 *  $this->template_dir must resolve to a directory that PHP has complete access to.
 *  	It will unlink and recreate files as necessary, which is typically only when
 *  	the template (.haml) file has been modified.
 */

if (!function_exists('xmlentities')) {
	function entify($match) {
		$c = $match[0];
		if ($c == "\n") return '<br />';
		if ($c{0} == ' ') return str_replace(' ', '&#160;', $c);

		$b0 = ord($c{0});	// Ordinal Value of first byte in character
		$bit = 0x80;		// Test Bit

		if (!($b0 & $bit)) return "&#$b0;";		// Save some work if it's 7-bit safe

		$bytes = 1;								// Number of Bytes in Unicode Character
		while ($b0 & ($bit >>= 1)) ++$bytes;	// Each bit indicates an additional byte

		// Mash all the bytes together into the 32-bit int we want
		$v = $b0 & (0x3F >> $bytes);			// Mask first byte
		for($i=1; $i<$bytes; ++$i) {
			$v = ($v << 6) | (ord($c{$i}) & 0x3F);
		}
		return sprintf('&#x%x;', $v);	// Multibyte chars are output in hex
	}

	function xmlentities($string, $nltobr = false) {
		return preg_replace_callback('~[\\\'\"&<>'.($nltobr ? '\n' : '').']|^ +| +$~Su','entify',$string);
	}
}

class PhammablePlugin {
	public $template_extension;
	public $variables;

	public function PhammablePlugin() {
		$this->template_extension = '.haml';
		$this->template_dir = 'views';
		$this->cache_dir = 'cache';
		$this->indent = "\t";
		$this->caching = 1;
	}

	public function render($file)
	{
		echo $this->parse($file);
	}

	public function assign($key, $value=false)
	{
		if(is_array($key)) {
			$values = $key;

			foreach($values as $key => $value) {
				$this->variables[$key] = $value;
			}
		} else {
			$this->variables[$key] = $value;
		}
	}

	public function haml_tagout($tag, &$atts) {
		if (empty($tag)) return false;
		echo '<', $tag;
		foreach($atts as $k=>$v) if ($v) echo " $k=\"$v\"";
		switch ($tag) {
			case 'link':
			case 'img':
			case 'hr':
			case 'br':
			case 'input':
			case 'col':
			echo ' />';
			return false;
		}
		echo '>';
		return true;
	}

	public function to_static($file)
	{
		ob_start();
		require $file;
		return ob_get_clean();
	}

	/**
	 * Find the target location for a HAML file on disk.
	 * If the cached file is up-to-date, simply return its filename.
	 * Otherwise, parse the HAML file and output equivalent PHP code to the target location, then return its filename.
	 */
	public function parse($file) {
		$file = $this->template_dir.'/'.$file.$this->template_extension;
		#$file = realpath($file);
		$encoding = 'utf-8';

		$paths = explode(':', ini_get('include_path'));
		$file_found = false;

		foreach($paths as $path) {
			$new_file = $path.'/'.$file;

			if(file_exists($new_file)) {
				$file_found = true;
				$file = $new_file;
				break;
			}
		}

		if (!$file_found) throw new Exception("File '$file' does not exist!");

		$php_file = str_replace('//', '/', $this->cache_dir.'/'.basename($file)).'.php';
		$static_file = rtrim($php_file, '.php').'.htm';

		if ($this->caching == 2 && file_exists($static_file)) {
			// If the file is up-to-date, bail.
			if (filemtime($file) < filemtime($static_file)) {
				return file_get_contents($static_file);
			}

			// If it isn't, delete the old cached version.
			unlink($static_file);

		} elseif ($this->caching == 1 && file_exists($php_file)) {
			// If the file is up-to-date, bail.
			if (filemtime($file) < filemtime($php_file)) {
				return $this->to_static($php_file);
			}

			// If it isn't, delete the old cached version.
			unlink($php_file);
		}

		// BEGIN Parsing and Compiling Code

		$relIndent = false;
		$tags = array();
		$iChr = "\t ";	// Allow tabs and spaces for indent.  When we encounter our first indent, this will change.
		$iLen = NULL;
		$level = 0;
		$useSrc = array('img', 'script', 'applet', 'embed', 'frame', 'iframe');
		$docTypes = array(
			'XML' =>
				'<?xml version="1.0" encoding="%s"?'.'>',
			'Strict' =>
				'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
			'Transitional' =>
				'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
			'Frameset' =>
				'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
			'1.1' =>
				'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
			'HTML Strict' =>
				'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
			'HTML Transitional' =>
				'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
			'HTML Frameset' =>
				'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
		);

		ob_start();

		// Copy assigned variables to the local scope.
		if(count($this->variables) > 0) {
			echo "<?php \n";
			foreach($this->variables as $key => $value) {
				echo '$'.$key.' = '.'$this->variables[\''.$key."'];\n";
			}
			echo '?'.">\n";
		}

		$lines = file($file);

		foreach($lines as $line) {

			if (strpos($line, '!!!') === 0) {
				$type = trim(substr($line, 4));
				if (empty($type)) $type = 'Transitional';

				// If the DOCTYPE doesn't look like it's in the list,
				// remove the last word off it and try again.
				if(!array_key_exists($type, $docTypes)) {
					$type_words = explode(' ', $type);
					$encoding = array_pop($type_words);
					$type = implode(' ', $type_words);

					if(!array_key_exists($type, $docTypes)) {
						throw new Exception("'$type' not in valid doctypes(".implode(',',array_keys($docTypes)).")");
					}
				}

				echo sprintf($docTypes[$type], $encoding), "\n";
				continue;
			}

			// Skip blank lines
			if (!trim($line)) continue;

			// First, we find our indent character and level...
			$lastLevel = $level;
			if ($iCount = strspn($line, $iChr)) {
				if (!$iLen) {
					$iChr = $line{0};
					// Only use divisors for spaces.
					$iLen = ($iChr == "\t") ? 1 : $iCount;
				}
				$level = $iLen > 1 ? (int)($iCount / $iLen) : $iCount;
				$indent = str_repeat($this->indent, $level);
			} else {
				$indent = "";
				$level = 0;
			}

			// Output all as-yet unclosed structures until we're back to the appropriate indent.
			while ($lastLevel > $level) {
				if ($lastLevel-- > count($tags)) continue;
				$tag = (array)array_pop($tags);
				$temp = str_repeat($this->indent, $lastLevel);
				foreach($tag as $t) {
					if ($t == '{') {
						// For close-braces, prevent excess whitespace by indenting within the PHP tag.
						echo "<?php ", @substr($temp, 6), "} ?>\n";
					} else echo ($t == 'pre') ? null : $temp, "</", $t, ">\n";
				}
			}
			$relIndent = in_array('pre', $tags) ? str_repeat($this->indent, $level - count($tags)) : false;

			// Find the length of our selectors, so we don't try to parse the payload...
			$i = $iCount;
			$_a = 0;
			switch ($line{$i}) {
			case '&':
				case '-':
				case '=':
					$i++;
					break;
				default:
					while (isset($line{++$i})) {
						switch ($line{$i}) {
						case '{': $_a++; break;
						case '}': $_a--; break;
						case "\t":
							case ' ': if ($_a <= 0) break 2;
						}
					}
			}

			// Start with a clean slate, but put id and class in to enforce ordering of attributes.
			$tag = null;
			$atts = array('id' => null, 'class' => null);

			// For all the syntax we care about, matched in order and according to priority...
			$tokens = preg_match_all('~:=|{([^}]++)}|(?:[%#\.:@]((?<=@)\S++|\w+)|[&=-] |//)~S',
				substr($line, $iCount, $i - $iCount + 1), $matches, PREG_SET_ORDER);
			$payload = rtrim(substr($line, $tokens ? $i + 1 : $iCount));
			foreach($matches as $m) {
				$c1 = empty($m[0]) ? null : $m[0]{0};	// First character of the full-match text.
				if ($m[0] == ':=') break;
				switch ($c1) {

				// Tag Identifiers
				case '%':	
					$tag = $m[2];
					break;

				case '#':	
					$atts['id'] = $m[2];
					break;

				case '.':	
					$atts['class'] = $atts['class'] ? "$atts[class] $m[2]" : $m[2];
					break;

				// Name, URI, and other attributes
				case ':':	
					$atts['name'] = $m[2];
					break;

				case '@':
					$atts[in_array($tag, $useSrc) ? 'src' : 'href'] = $m[2];
					break;

				case '{':	// Parse attributes within braces.
					if (preg_match_all('~([\w-:]+)\s*=>?\s*(("|\')([^\'"]++)("|\')|`([^`]++)`|\S+)~', $m[1], $matches, PREG_SET_ORDER)) {
						foreach ($matches as $att) {
							$att[1] = ltrim($att[1], ':');
							if($att[3] == "'" || $att[3] == '"') {
								unset($att[2]);
								unset($att[3]);
								unset($att[5]);
								$att = array_values($att);
							}

							if (isset($att[4])) {
								$atts[$att[1]] = "<?php echo $att[4]; ? >";
							} else {
								$atts[$att[1]] = isset($att[3]) ? $att[3] : $att[2];
							}
						}
					}
					break;

				// From here on, the argument won't be part of the match; We deal with the payload.
				case '=': $payload = "echo $payload";
				case '-': // PHP Code, as-is (fallthrough from Echo case);
				// Indent appropriately, outputting the tag if it's there.
					if (($c1 == '=') || $tag) {
						echo $indent;
						$open = $this->haml_tagout($tag, $atts);
						echo '<?php ';
					} else {
						echo '<?php ', @substr($indent, 0, -6);
						$open = false;
					}

					$openBrace =
						preg_match('~^(?:if|for(each)?|while|do|switch|else(if)?|function)\b\s*\(.*?\)\s*$~', $payload);

					if ($openBrace) {
						echo "$payload { ?>";
					} else {
						if ($payload{strlen($payload) - 1} != ';') $payload .= ';';	// Help out with line-endings.
						echo "$payload ?>";
					}

					if ($open && $openBrace) {
						$tags[] = array('{', $tag);
					} elseif ($open) {
						if ($payload) echo "</$tag>";
						else $tags[] = $tag;
					} elseif ($openBrace) $tags[] = '{';

					$tag = null;
					$payload = null;
					break;

				case '/':	// HTML Comment
					echo $indent, "<!-- $payload -->";
					$payload = null;
					break;

				case '&':	// FILE INCLUSION!!
					preg_match('~(?<=\.)\w+$~', $payload, $ext);
					switch (strtolower($ext[0])) {
					case 'php':
						echo "<?php include '".realpath($payload)."'; ?>";
						break;
					case 'html':
						case 'txt':
							echo "<?php readfile('".realpath($payload)."'); ?>";
							break;
						case 'haml':
							echo "<?php include haml('".realpath($payload)."'); ?>";
							break;
						default:
							throw new Exception("'$ext' is not a recognized type for inclusion!");
					}
					$payload = null;
					break;
				}

				// Make sure the default tag is used when needed.
				switch($c1) {
					case '@':
						// This could be changed to warn and fall through if desired.
						if (empty($tag)) throw new Exception('Illegal Syntax: URI provided without Tagname!');
					case '{':
					case '#':
					case '.':
					case ':':
						if (empty($tag)) $tag = 'div';
				}
			}

			if ($payload) {
				echo $relIndent === false ? $indent : $relIndent;
				$open = $this->haml_tagout($tag, $atts);
				echo xmlentities($payload), ($open) ? "</$tag>" : '';

			} elseif ($tag) {
				echo $indent;
				if ($this->haml_tagout($tag, $atts)) {
					$tags[] = $tag;
					$indent .= $this->indent;
				}
			}

			echo "\n";
		}

		// Close out the document. This code should be VERY similar to the indentation management code.
		while (!empty($tags)) {
			$tag = (array)array_pop($tags);
			$temp = str_repeat($this->indent, count($tags));
			foreach($tag as $t) {
				if ($t == '{') {
					// For close-braces, prevent excess whitespace by indenting within the PHP tag.
					echo '<?php ', @substr($temp, 6), "} ?>\n";
				} else echo ($t == 'pre') ? null : $temp, '</', $t, ">\n";
			}
		}

		// Store the created code on disk, and return the location of the temp file.
		$pArr = explode('/', $php_file, -1);
		$current = '';

		foreach ($pArr as $next) {
			$current .= $next.'/';
			if (!file_exists($current)) mkdir($current);
		}

		// Dump the PHP code in the output buffer to a variable instead of outputing it...
		$php = preg_replace("~\?>([\r\n\s]+)<\?php~", "\\1\t", ob_get_clean());

		// ...and write the variable to disk.
		file_put_contents($php_file, $php);

		// Parse the php file and save it to an html file.
		$html = $this->to_static($php_file);
		file_put_contents($static_file, $html);

		return $html;
	}
}

?>
