<?php
/**
 * Patterns are key-value array pairs, with the key being a specially
 * formatted regex pattern to match against the url, and the value
 * being an action in the format "Controller.method".
 */
$this->add_patterns(array(
	# This is the default index page, `/`.
    '' => 'Default.index',

	# Everything in a pattern is treated literally,
	# except text in the format "(regex:name)".
	#
	# "regex" can be almost any valid regex, though
	# don't use the start/ending like characters
	# `$`, and `^`, or delimiters (like /regex/),
	# as they're automatically added.
	#
	# "name" can be any alphanumeric string,
	# and is the key the capture is given in the
	# controller's $this->url array.

	# Anything captured by the pattern is put in
	# the $this->url array in the controller
	# 'chickens/([0-9]+:id)' => 'Chickens.detail',
	#
	# "/chickens/42" would result in $this->url['id'] = 42.

	# It is possible to back-reference a capture in
	# the action like this:
	# 'blog/((archives|admin):page)' => 'Blog.(page)'
	#
	# This would result in "Blog.archives" being called
	# when the user visted "/blog/archives".
	#
	# Back-references arn't limited to the method:
	# '((blog|store):section)/((archives|admin):page)/([0-9]+:type)' => '(section).(page)(type)'
	#
	# It's not reccomended to dynamically set the controller
	# though, as it can cause security problems. Also,
	# it's usually better to access things like the "type"
	# capture from $this->url inside the controller, rather
	# than creating methods for every single case.
));

/**
 * These are the controllers to use when an error is encountered.
 */
$this->error_handlers = array(
	'default' => 'Default.error'

	# You can also specify an HTTP error code to respond to, like below.
	# 404 => 'Kaboom.four_oh_four'
);
?>
