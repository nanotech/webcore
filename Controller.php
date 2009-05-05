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

	protected function render($view, $group='views')
	{
		echo $this->display->render($view, $group);
	}

	public function redirect_to($action, $values=array())
	{
		if (strpos($action, '/') !== false) {
			$uri = $action;
		} else {
			$uri = self::link_to($action, $values);
		}

		header('Location: '.$uri);
	}

	public static function link_to($action, $values=array())
	{
		global $Director;

		$pattern = $Director->get_pattern($action);
		$pattern = $pattern[1]; # get structure

		if (count($values) != 0) {
			$uri = Director::replace_variables($pattern, $values);
		} else {
			$uri = $pattern;
		}

		$uri = BASE_URL.'/'.$uri;

		return $uri;
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
