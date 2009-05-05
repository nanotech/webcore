<?php
/**
 * Directs urls to their assigned controllers
 */
class Director
{
	public $patterns; # usually loaded from app/config/router.php
	public $parsed_patterns; # in-memory cache
	public $error_handlers; # actions to use in case of errors

	public function Director($config=false)
	{
		$this->patterns = array();
		$this->error_handlers = array(
			'default' => 'Default.error'
		);

		if ($config) {
			require_once $config;
		}
	}

	/**
	 * Add new patterns by merging the arrays.
	 */
	public function add_patterns($new_patterns)
	{
		$this->patterns = array_merge($this->patterns, $new_patterns);
		return true;
	}

	/**
	 * Read the given url and apply the actions in
	 * the $patterns array to it.
	 */
	public function parse($url=false)
	{
		if ($url == false) $url = Director::get_uri();

		try {
			// Loop through the patterns.
			// The array key is the pattern, and the 
			// value is the action.
			foreach ($this->patterns as $pattern => $action)
			{
				$parsed_pattern = self::parse_pattern($pattern, $action);
				$this->parsed_patterns[$action] = $parsed_pattern;
				list($regex, $structure) = $parsed_pattern;

				if (preg_match($regex, $url, $parameters))
				{
					$action = self::parse_action($action, $parameters);

					if ($controller = $this->execute($url, $action, $parameters)) {
						return true;
					} else {
						throw new HttpError(404);
					}
				}
			}

			// If we're here, we didn't find any matches,
			// so we'll throw an error.
			throw new HttpError(404);

		// Catch exceptions and display the page for that error.
		} catch (HttpError $e) {
			if(!empty($url) && is_file('static/'.$url)) {
				readfile('static/'.$url);
				return true;
			}

			$error_code = $e->getMessage();
			$handlers = &$this->error_handlers;

			if (isset($handlers[$error_code])) {
				$handler = $handlers[$error_code];
			} else {
				$handler = $handlers['default'];
			}

			if (!$this->execute($url, $handler, array('code' => $error_code), false)) {
				die('Missing error handler "'.$handler.'"!');
			}

			return false;
		}
	}

	protected function execute($url, $action, $parameters=NULL, $cache=NULL)
	{
		global $config;
		list($class, $method) = explode('.', $action);

		if ($cache === NULL) {
			$cache = ($config['cache']['level'] == 'url');
			$cache_expiry = $config['cache']['expiry'];
		}

		try {
			if ($cache) {
				$cache_file = $config['cache']['directory'].'/'.sha1($url);
				$buffering = false;
			}

			if ($cache && file_exists($cache_file) &&
				(time() - filemtime($cache_file)) < $cache_expiry) {

				readfile($cache_file);
				return true;

			} else {
				if ($cache) {
					ob_start(); $buffering = true;
				}

				Core::import($class);
				$controller_name = $class.'Controller';
				$controller = new $controller_name($parameters);

				if (isset($parameters[0]))
					unset($parameters[0]); # Don't pass the complete match
				call_user_func_array(array(&$controller, $method), $parameters);

				if ($cache) {
					file_put_contents($cache_file, ob_get_flush());
					$buffering = false;
				}

				return $controller;
			}
		} catch (MissingResource $e) {
			if ($cache && $buffering) {
				ob_end_flush();
				$buffering = false;
			}

			return false;
		}
	}

	/**
	 * Get the current uri.
	 */
	public static function get_uri()
	{
		$maybe_uris = @array(
			$_SERVER['PATH_INFO'],
			$_SERVER['QUERY_STRING'],
			$_SERVER['ORIG_PATH_INFO']
		);

		foreach($maybe_uris as $uri) {
			$uri = trim($uri, '/');

			if (!empty($uri)) {
				return $uri;
			}
		}

		if (is_array($_GET) && count($_GET) == 1 &&
			($uri = trim(key($_GET), '/')) != '') {

			return $uri;
		}

		return '';
	}

	/**
	 * Parse a custom-syntax pattern into a regex and an array.
	 */
	public static function parse_pattern($pattern)
	{
		# Find regex patterns
		preg_match_all('/\((.*?)(?:\:([^)]+))?\)/u', $pattern, $matches);

		# Convert patterns to valid regex and set named captures
		$c = count($matches[0]);
		$regex = $sections = $pattern; # copy $pattern for manipulation

		for ($i=0;$i<$c;++$i) {
			$name = $matches[2][$i];
			$old_pattern = $matches[0][$i];

			$subpattern = '(?P<'.$name.'>'.$matches[1][$i].')';
			$regex = str_replace($old_pattern, $subpattern, $regex);
			$sections = str_replace($old_pattern, '('.$name.')', $sections);
		}

		# Add dilemters, flags.
		$regex = '!^'.$regex.'$!ui';

		return array($regex, $sections);
	}

	/**
	 * Parse a Controller.method action, replacing variables.
	 */
	public static function parse_action($action, $values)
	{
		# Parse the variables in the action
		$action = self::replace_variables($action, $values);

		# Ensure the first letter of the controller is uppercase
		$action = ucfirst($action);

		return $action;
	}

	/**
	 * Find and replace named variables in a string with the format `(var)`.
	 */
	public static function replace_variables($string, $values)
	{
		# Find any variables
		preg_match_all('/\(([^)]+)\)/u', $string, $matches);

		# Replace variables with their values
		foreach ($matches[1] as $id => $var) {
			if (isset($values[$var])) {
				$string = str_replace($matches[0][$id], $values[$var], $string);
			}
		}

		return $string;
	}

	/**
	 * Get the pattern of an action.
	 */
	public function get_pattern($action)
	{
		$pattern = &$this->parsed_patterns[$action];
		if (isset($pattern)) {
			return $pattern;
		} else {
			$patterns = array_flip($this->patterns);
			$pattern = self::parse_pattern($patterns[$action]);
			return $pattern;
		}
	}
}

class HttpError extends Exception {}
?>
