<?php
class Config {

	private $config;
	
	public function __construct($config) {
		$this->config = $config;
	}

	public function __get($name) {
		return @$this->config[$name];
	}
}
?>
