<?php
class Controller
{
	public $display;
	public $url;

	public function Controller($url=false)
	{
		global $Display;

		$this->display = &$Display;
		$this->url = $url;
	}

	protected function render($view)
	{
		if ($this->url) $this->display->meta['THIS'] = $this->url[0];
		echo $this->display->render($view);
	}

	public function redirect_to($url)
	{
		header('Location: '.BASE_URL.'/'.$url);
	}

	public function export($name, $value=NULL)
	{
		if (is_array($name)) {
			$variables = $name;
			unset($name);

			foreach($variables as $name => $value) {
				$this->display->meta[$name] = $value;
			}
		} else {
			$this->display->meta[$name] = $value;
		}
	}
}
?>
