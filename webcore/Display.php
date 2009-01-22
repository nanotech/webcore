<?php
class Display
{
	public $engine;
	public $engine_name;
	protected $Layout;
	protected $layout_name;

	public function Display($engine)
	{
		// Plug in template system
		Core::import($engine);
		$this->engine_name = str_replace('.plugin', '', $engine);
		$plugin_name = $this->engine_name.'Plugin';
		$this->engine = new $plugin_name;
        $this->engine->assign('BASE_URL', BASE_URL);
	}

	public function page($page)
	{
		# If there's a layout set...
		if (!empty($this->layout_name)) {

			# Render the page, and save it as $content.
			$content = $this->engine->render($page, false);

			# Copy the layout name variable so we can use it
			# after we unset it below.
			$layout_name = $this->layout_name;

			# Set to null so we don't go into an infinite loop.
			$this->layout_name = null;

			# Call the layout's function, passing the original
			# page content.
			$this->Layout->$layout_name($content);

		} else {
			# If a layout isn't set, just render the page.
			$this->engine->render($page);
		}
	}

	public function assign($key, $value=false)
	{
		if($value)
			$this->engine->assign($key, $value);
		else
			$this->engine->assign($key);
	}

	public function setLayout($layout)
	{
		$template_dir = $this->engine->template_dir;
		$template_extension = $this->engine->template_extension;
		$layout_file = $template_dir.'/layouts/'.$layout.$template_extension;

		if (is_file($layout_file)) {
			$this->layout_name = $layout;
			Core::import('Layout.controller');
			$this->Layout = new LayoutController();
		} else {
			throw new Exception("Layout '$layout' does not exist.");
		}
	}
}
?>
