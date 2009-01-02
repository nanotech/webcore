<?php
/**
 * You can use this class by putting the "libs" folder
 * from the Smarty package in the plugins directory
 * and renaming it to "Smarty".
 */

require_once('Smarty/Smarty.class.php');

class SmartyPlugin extends Smarty { 
	public $template_dir;
	public $template_extension;

	public function SmartyPlugin()
	{
		$this->Smarty();

		$this->template_dir = 'app/views';
		$this->template_extension = '.htm';
		$this->compile_dir = 'cache';
		$this->cache_dir = 'cache';
		$this->config_dir = 'plugins/smarty/configs';
		$this->assign('BASE_URL', BASE_URL);
	}

	public function render($file, $display=true)
	{
		$file = $file.$this->template_extension;

		if ($display) {
			return $this->display($file);
		} else {
			return $this->fetch($file);
		}
	}
}
?>
