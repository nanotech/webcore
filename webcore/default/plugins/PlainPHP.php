<?php
#
# Simple, plain PHP template plugin.
#
class PlainPHPPlugin {
	public $template_dir;
	public $template_extension;
	public $variables;

	public function __construct()
	{
		$this->template_dir = 'app/views';
		$this->template_extension = '.php';
		$this->assign('BASE_URL', BASE_URL);
	}

	public function render($file, $display=true)
	{
		if ($display) {
			return $this->display($file);
		} else {
			return $this->parse($file);
		}
	}

	public function display($file)
	{
		$this->file = $this->template_dir.'/'.$file.$this->template_extension;

		foreach($this->variables as $key => $value) {
			${$key} = $value;
		}

		include $this->file;
	}

	public function parse($file)
	{
		ob_start();
		$this->display($file);
		return ob_get_flush();
	}

	public function assign()
	{
		$args = func_get_args();

		if (func_num_args() == 1) {
			foreach($args as $key => $value) {
				$this->variables[$key] = $value;
			}
		} else {
			$this->variables[$args[0]] = $args[1];
		}
	}
}
?>
