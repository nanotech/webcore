<?php
/**
 * In this file, you can write code to run before your controllers do.
 *
 * If you want to learn more about what executes when, have a look at
 * `webcore/run.php`, where webcore/ is your WebCore installation. You can
 * find where WebCore is installed from your `index.php` file.
 *
 * Below, we initialize a Display object, and pass an array of Filters to it.
 */

$Display = new Display(array(

	# Some filters allow you to specify options for a filter by using an
	# array. The first element of the array is the name of the filter, and
	# the rest are passed as arguments to the filter's parse method.
	#
	# In the Layout filter, the first argument (the second element) is the
	# name of the view to use as the layout. The second argument is an
	# array of filters, as Layout creates it's own Display to render the
	# layout view with.
	array('Layout', 'main', array('PlainPHP')),

	# Simpler filters just require you to specify the filter's name.
	'RewriteAbsoluteLinks',
));
?>
