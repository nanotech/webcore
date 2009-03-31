<?php
/**
 * Filters are chainable data processors used in Display.
 * You could compare them to template engines in other web frameworks.
 */
abstract class Filter {
	public $meta;

	public function __construct($meta) {
		$this->meta = $meta;
	}

	public function parse($data) {
		return $data;
	}
}
?>
