<?php
class Display
{
	public $engine;
	public $engine_name;

	public function Display($engine)
	{
		// Plug in template system
		Core::import($engine);
		$this->engine_name = rtrim($engine, '.plugin');
		$plugin_name = $this->engine_name.'Plugin';
		$this->engine = new $plugin_name;
	}

	public function page($page)
	{
		$this->engine->render(
			(string) $page.$this->engine->template_extension
		);
	}

	public function assign($key, $value=false)
	{
		if($value) 
			$this->engine->assign($key, $value);
		else 
			$this->engine->assign($key);
	}
}
?>
