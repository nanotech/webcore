<?php
if(DEBUG) error_reporting(E_ALL);
set_include_path('.:app');

require_once('constants.php');
require_once('router.php');
require_once('webcore/Core.php');

$Display = new Display('Phammable.plugin');

$requesturl = (string) $_GET['url'];
$page = Director::parse($requesturl, $patterns, $notfound);
?>
