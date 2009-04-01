<?php
error_reporting(E_ALL);
date_default_timezone_set('America/Regina');

return array(
	'base-url' => '/',
	'debug' => true,
	'cache' => array(
		'level' => 'none', # available: url, none
		'resources' => false,
		'directory' => APP_DIR.'/cache',
	),
);
?>
