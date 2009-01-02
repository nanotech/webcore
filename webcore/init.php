<?php
set_include_path('.:app:webcore/default:');

require_once('config/constants.php');
if(DEBUG) error_reporting(E_ALL);

require_once('webcore/Core.php');

session_start();

# Import the Director (Router), Display (View),
# and Controller, and Database (Model).
Core::import('Director', 'Display', 'Controller', 'Database');
?>
