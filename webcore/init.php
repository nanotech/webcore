<?php
set_include_path('.:app:webcore/default');

require_once('config/constants.php');
if(DEBUG) error_reporting(E_ALL);

require_once('webcore/Core.php');

# Import the Router (Director), View (Display), and Controller.
# The Model (Database) can be imported by the programmer later.
Core::import(array('Director', 'Display', 'Controller'));
?>
