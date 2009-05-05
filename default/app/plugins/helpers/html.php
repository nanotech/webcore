<?php
/**
 * Converts a PHP array to a HTML attribute string.
 */
if (!function_exists('html_attrs'))
{
	function html_attrs($array, $exclude=array())
	{
		$attrs = array();

		foreach ($array as $k => $v) {
			if (in_array($k, $exclude) || $k{0} == '_') continue;
			$attrs[] = $k.'="'.htmlentities($v).'"';
		}

		return implode(' ', $attrs);
	}
}
?>
