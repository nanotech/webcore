<?php
require_once('webcore/init.php');

$Display = new Display('PlainPHP.plugin');

$Director = new Director();
$page = $Director->parse((string) $_GET['url']);
?>
