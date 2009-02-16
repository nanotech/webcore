<?php
define('WEBCORE_DIR', dirname(__FILE__));
if (!defined('APP_DIR') || APP_DIR === false) die('Please define APP_DIR.');

set_include_path(get_include_path().'.:'.APP_DIR.':'.WEBCORE_DIR.'/default:');

require 'webcore/Core.php';
require 'config/constants.php';

Core::index_resources(array(
	'php' => array(
		WEBCORE_DIR,
		APP_DIR.'/default/controllers',
		APP_DIR.'/default/models',
		APP_DIR.'/default/plugins',
		WEBCORE_DIR.'/default/controllers',
		WEBCORE_DIR.'/default/plugins',
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
