<?php
class Controller
{
	public $Display;

	public function Controller()
	{
		$this->Display = &$Display;
	}

	protected function render($view)
	{
		global $Display;

		$Display->page($view);
	}

	public function assign($key, $value=false)
	{
		global $Display;

		if($value) 
			$Display->assign($key, $value);
		else 
			$Display->assign($key);
	}
}
?>
