<?php
if (!function_exists('link_to'))
{
	function link_to($action, $values=array())
	{
		return Controller::link_to($action, $values);
	}
}

if (!function_exists('link_list'))
{
	/**
	 * Loops through an array of links in "Name" => "Controller.method" 
	 * format, and either returns or echos it.
	 */
	function link_list($links, $before='<li>', $after="</li>\n", $return=false)
	{
		$string = '';
		foreach ($links as $name => $link) {
			$string .= $before.'<a href="'.link_to($link).'">'.$name.'</a>'.$after;
		}

		if ($return) {
			return $string;
		} else {
			echo $string;
		}
	}
}
?>
