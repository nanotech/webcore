<?php
/**
 * Patterns are key-value array pairs, with the key being a regex to
 * match against the url, and the value being a controller in the
 * format "Controller.method".
 */
$this->add_patterns(array(
    '!^$!' => 'Default.index',

	# Anything captured by the regex is passed to
	# the controller as arguments. Named captures
	# are not yet supported.
    # '!^chickens/([0-9]+)$!' => 'Chickens.detail',
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
