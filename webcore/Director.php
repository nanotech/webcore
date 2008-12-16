<?php
class Director
{
	public static function parse($url, $patterns, $notfound = false)
	{
		$url = trim($url, '/');

		foreach($patterns as $pattern => $action)
		{
			if(preg_match($pattern, $url, $result))
			{
				return Director::execute($action, $result);
			}
		}

		Director::execute($notfound);
	}

	protected static function execute($action, $result = false)
	{
		$action = explode('.', $action);

		require_once('controllers/'.$action[0].'.php');
		$controller_name = $action[0].'Controller';
		$controller = new $controller_name();

		$controller->$action[1]($result);

		return $result;
	}
}
?>
