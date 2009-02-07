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
	public function parse($url)
	{
		try {
			$url = trim($url, '/');

			if(!empty($url) && file_exists('static/'.$url)) {
				readfile('static/'.$url);
				return true;
			}

			// Loop through the patterns.
			// The array key is the pattern, and the 
			// value is the action.
			foreach($this->patterns as $pattern => $action)
			{
				if(preg_match($pattern, $url, $result))
				{
					$success = $this->execute($url, $action, $result);

					if ($success == false) {
						throw new HttpError(404);
					} else {
						return $success;
					}
				}
			}

			// If we're here, we didn't find any matches,
			// so we'll throw an error.
			throw new HttpError(404);

		// Catch exceptions and display the page for that error.
		} catch (HttpError $e) {
			$error_code = $e->getMessage();
			$handlers = &$this->error_handlers;

			if (isset($handlers[$error_code])) {
				$handler = $handlers[$error_code];
			} else {
				$handler = $handlers['default'];
			}

			return $this->execute($url, $handler, $error_code, false);
		}
	}

	protected function execute($url, $action, $result = true, $cache = CACHE)
	{
		$action = explode('.', $action);

		try {
			if ($cache) {
				$cache_file = APP_DIR.'/../cache/'.sha1($url);
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

				Core::import($action[0].'.controller');
				$controller_name = $action[0].'Controller';
				$controller = new $controller_name($result);

				$controller->$action[1]($result);

				if ($cache) {
					file_put_contents($cache_file, ob_get_flush());
					$buffering = false;
				}
			}
		} catch (MissingClass $e) {
			if ($cache && $buffering) {
				ob_end_flush();
				$buffering = false;
			}

			return false;
		}

		return $result;
	}
}

class HttpError extends Exception {}
?>
