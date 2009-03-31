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

if (!function_exists('underscore'))
{
	function underscore($text) {
		return strtolower(str_replace(' ', '_', $text));
	}
}

if (!function_exists('deunderscore'))
{
	function deunderscore($text) {
		return ucwords(str_replace('_', ' ', $text));
	}
}
?>
