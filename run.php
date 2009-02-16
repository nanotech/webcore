<?php
define('WEBCORE_DIR', dirname(__FILE__));
if (!defined('APP_DIR') || APP_DIR === false) die('Please define APP_DIR.');

set_include_path(get_include_path().'.:'.APP_DIR.':'.WEBCORE_DIR.'/default:');

require WEBCORE_DIR.'/Core.php';
require 'config/constants.php';

Core::index_resources(array(
	'php' => array(
		WEBCORE_DIR.'/default/app/controllers',
		WEBCORE_DIR.'/default/app/plugins',
		APP_DIR.'/controllers',
		APP_DIR.'/models',
		APP_DIR.'/plugins',
		WEBCORE_DIR,
	),
	'views' => array(
		APP_DIR.'/views',
		WEBCORE_DIR.'/default/views',
	)
));

require 'config/setup.php';

$Director = new Director();
$Director->parse();
?>
