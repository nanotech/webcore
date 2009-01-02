<?php
class Director
{
	public $patterns;
	public $error_handlers;

	public function Director()
	{
		$this->patterns = array();
		$this->error_handlers = array(
			404 => 'Default.error'
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
					return $this->execute($action, $result);
				}
			}

			// If we're here, we didn't find any matches,
			// so we'll throw an error.
			throw new HttpError(404);

		// Catch exceptions and display the page for that error.
		} catch (HttpError $e) {
			$error_code = $e->getMessage();
			$handler = $this->error_handlers[$error_code];

			return $this->execute($handler);
		}
	}

	protected function execute($action, $result = false)
	{
		$action = explode('.', $action);

		require_once('controllers/'.$action[0].'.php');
		$controller_name = $action[0].'Controller';
		$controller = new $controller_name($result);

		$controller->$action[1]($result);

		return $result;
	}
}

class HttpError extends Exception {}
?>
