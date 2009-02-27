<?php
/**
 * Functions for processing text for output.
 */
if (!function_exists('paragraphize'))
{
	function paragraphize($text){
		return '<p>'.preg_replace("/(\r\n|\n|\r){2}/", "</p>\n\n<p>", $text)."</p>\n";
	}
}
?>
