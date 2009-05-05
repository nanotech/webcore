<?php
error_reporting(E_ALL);

return array(
	//'base_url' => '',
	'debug' => true,
	'cache' => array(
		'level' => 'none', # available: url, none
		'resources' => false,
		'expiry' => 3600,
		'directory' => APP_DIR.'/cache',
	),
	'parsers' => array(
		'php' => 'PlainPHP',
	),
	'mimes' => array(
		'html' => 'text/html',
		'htm' => 'text/html',
	),
);
?>
