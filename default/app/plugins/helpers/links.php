<?php
if (!function_exists('link_to'))
{
	function link_to($action, $values=array())
	{
		global $Director;

		$pattern = $Director->get_pattern($action);
		$pattern = $pattern[1]; # get structure
		if (count($values) != 0)
			$uri = Director::replace_variables($pattern, $values);
		$uri = BASE_URL.'/'.$uri;

		return $uri;
	}
}
?>
