<?php
set_include_path('.:app:webcore/default:');
require 'config/constants.php';
require 'webcore/Core.php';
session_start();
Core::import('Director.core', 'Display.core', 'Controller.core');
?>
