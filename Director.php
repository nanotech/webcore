<?php
class Director
{
	public $patterns;
	public $error_handlers;

	public function Director()
	{
		$this->patterns = array();
		$this->error_handlers = array(
			'default' => 'Default.error'
		);

		require_once('config/router.php');
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
			foreach($this->patterns as $pattern => $action)
			{
				if(preg_match($pattern, $url, $parameters))
				{
					if ($this->execute($url, $action, $parameters)) {
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

			if (!$this->execute($url, $handler, $error_code, false)) {
				die('Missing error handler "'.$handler.'"!');
			}

			return false;
		}
	}

	protected function execute($url, $action, $parameters=NULL, $cache=CACHE)
	{
		list($class, $method) = explode('.', $action);

		try {
			if ($cache) {
				$cache_file = CACHE_DIR.'/'.sha1($url);
				$cache_expiry = 60 * 60 * 60;
				$buffering = false;
			}

			if ($cache && file_exists($cache_file) &&
				(time() - filemtime($cache_file)) < $cache_expiry) {

				readfile($cache_file);

			} else {
				if ($cache) {
					ob_start(); $buffering = true;
				}

				Core::import($class);
				$controller_name = $class.'Controller';
				$controller = new $controller_name($parameters);

				unset($parameters[0]); # Don't pass the complete match
				call_user_func_array(array(&$controller, $method), $parameters);

				if ($cache) {
					file_put_contents($cache_file, ob_get_flush());
					$buffering = false;
				}
			}
		} catch (MissingResource $e) {
			if ($cache && $buffering) {
				ob_end_flush();
				$buffering = false;
			}

			return false;
		}

		return true;
	}

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
}

class HttpError extends Exception {}
?>
