<?php
class Controller
{
	public $Display;
	public $url;

	public function Controller($url=false)
	{
		global $Display;

		$this->Display = &$Display;
		$this->url = $url;
	}

	protected function render($view)
	{
		if ($this->url) $this->Display->meta['THIS'] = $this->url[0];
		echo $this->Display->render($view);
	}

	public function redirect_to($url)
	{
		header('Location: '.BASE_URL.'/'.$url);
	}
}
?>
