<?php
class Controller
{
	public $Display;
	public $url;

	public function Controller($url=false)
	{
		$this->Display = &$Display;
		$this->url = $url;
	}

	protected function render($view)
	{
		global $Display;

		if ($this->url) $this->assign('THIS', $this->url[0]);
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

	public function location($url)
	{
		header('Location: '.BASE_URL.'/'.$url);
	}
}
?>
