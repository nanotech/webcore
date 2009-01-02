<?php
require_once('webcore/init.php');
set_include_path('.:app:webcore/default:');

$Display = new Display('Phammable.plugin');

$Director = new Director();
$page = $Director->parse((string) $_GET['url']);
?>
