<?php
define('WEBCORE_DIR', dirname(__FILE__));
if (!defined('APP_DIR')) die('Please define APP_DIR.');

set_include_path(get_include_path().'.:'.APP_DIR.':'.WEBCORE_DIR.'/default:');

require 'webcore/Core.php';
require 'config/setup.php';

$Director = new Director();
$Director->parse($_GET['url']);

Core::import('Director.core', 'Display.core', 'Controller.core');
?>
