<?php
/**
 * Layout filter, creates a new Display and puts
 * the current data into it.
 */
class Layout extends Filter {

	static public $type = 'a -> b';

	public function parse($data, $file, $filters=array(), $content_var='the_content')
	{
		$display = new Display((array) $filters);
		$display->meta = $this->meta;
		$display->meta[$content_var] = $data;
		return $display->render('layouts/'.$file);
	}
}
?>
