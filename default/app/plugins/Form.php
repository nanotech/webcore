<?php
class Form
{
	public $action;
	public $method;
	public $messages;
	public $submit = 'Submit';
	public $view;
	public $default_validator = 'text';

	private $inputs;

	private $html_types = array(
		'password' => 'password',
		'select' => 'select',
		'checkbox' => 'checkbox',
		'radio' => 'radio',
		'hidden' => 'hidden',
	);

	public static $validators = array(
		'text' => array('Form', 'text_validator'),
		'number' => array('Form', 'number_validator'),
		'multiple_choice' => array('Form', 'multiple_choice_validator'),
		'email' => array('Mailer', 'validate_email'),
	);

	public static $validation_mappings = array(
		'select' => 'multiple_choice',
		'checkbox' => 'multiple_choice',
		'radio' => 'multiple_choice',
		'alphanum' => 'text',
		'text' => 'text',
		'integer' => 'number',
		'number' => 'number',
		'email' => 'email',
	);

	public $error_messages = array(
		'invalid' => '"%s" is invalid.',
		'required' => '"%s" is required.',
	);

	public function __construct($inputs=array(), $action=false, $method='post', $view='forms/_table_form')
	{
		Core::import('helpers/html', 'helpers/text');

		$this->setInputs($inputs);
		$this->action = $action ? $action : $_SERVER['REQUEST_URI'];
		$this->method = strtolower($method);
		$this->view = $view;
	}

	public function setInputs($entries)
	{
		$this->inputs = array();

		foreach ($entries as $k => $v) {
			$this->addInput($k, $v);
		}
	}

	public function addInput($name, $opts)
	{
		$simple_opts = is_string($opts);

		if (is_int($name)) {
			$name = $simple_opts ? $opts : $opts['type'];
		}

		$id = self::makeId($name);

		if ($simple_opts) {
			$opts = array();
		}

		if (!isset($opts['type'])) {
			$opts['type'] = $id;
		}

		$html = array();
		$html['type'] = (isset($this->html_types[$opts['type']])) ? $this->html_types[$opts['type']] : 'text';
		$opts['validation_type'] = (isset(self::$validation_mappings[$opts['type']])) ? self::$validation_mappings[$opts['type']] : $this->default_validator;

		if (!isset($html['id'])) {
			$html['id'] = $id;
		}

		if (!isset($html['name'])) {
			$html['name'] = $html['id'];
		}

		if (!isset($opts['value'])) {
			$opts['value'] = '';
		}

		$opts['html'] = $html;
		$this->inputs[$name] = $opts;
	}

	public function getValue($name)
	{
		if (isset($this->inputs[$name])) {
			return $this->inputs[$name]['value'];
		} else {
			return null;
		}
	}

	public function getError($name)
	{
		if (isset($this->inputs[$name])) {
			return sprintf($this->error_messages[$this->inputs[$name]['error']], $name);
		} else {
			return null;
		}
	}

	public function getInput($name)
	{
		if (isset($this->inputs[$name])) {
			return $this->inputs[$name];
		} else {
			return null;
		}
	}

	public function __toString() {
		return $this->render();
	}

	public function render($id=false, $view=false, $display=false)
	{
		global $Display;

		if ($view === false) {
			$view = $this->view;
		}

		if ($display === false) {
			$display = new Display(array('PlainPHP'));
		} elseif (is_array($display)) {
			$filters = $display;
			$display = new Display($filters);
		}

		$display->meta += array(
			'action' => $this->action,
			'method' => $this->method,
			'id' => $id,
			'submit' => $this->submit,
		);

		$display->meta['inputs'] = $this->inputs;
		return $display->render($view);
	}

	public function input($name)
	{
		$input = $this->inputs[$name];
		return '<input type="'.$input['html']['type'].'" '.html_attrs($input['html']).' />';
	}

	public function submitted()
	{
		$data = $this->getData();
		foreach ($this->inputs as $id => $input) {
			if (!isset($data[$input['html']['id']])) return false;
		}

		return true;
	}

	public function validate($data=null)
	{
		if ($data === null) {
			$data = $this->getData();
		}

		$valid = array();
		$invalid = array();

		foreach ($this->inputs as $name => &$input) {
			$id = $input['html']['id'];
			if (!isset($data[$id])) continue;
			$new_value = $data[$id];

			if (
				isset($input['translate_keys'])
				&& isset($input['options'])
				&& is_array($input['options'])
				&& $input['translate_keys'] === true
			) {
				$new_value = $input['options'][$new_value];
			}

			// Check for values identical to their names.
			if ($new_value === $name) {
				$new_value = '';
			}

			if (!isset(self::$validators[$input['validation_type']])) {
				throw new FormValidationException('Missing validator!');
			}

			// Check if field is required.
			$required = isset($input['required']) && $input['required'] == true;

			// Get validator function.
			$validator = self::$validators[$input['validation_type']];

			try {

				if ($required && ($new_value === NULL || $new_value === '' || $new_value === array())) {
					throw new FormValidationException('required');
				}

				// Check for invalid values
				if (
					isset($input['invalid']) &&
					in_array($new_value, (array) $input['invalid'])
				) {
					throw new FormValidationException('invalid');
				}

				$result = call_user_func($validator, $new_value, $input);

				if ($result === false) {
					throw new FormValidationException('invalid');
				} else if ($result === true) {
					$input['value'] = $new_value;
				} else {
					$input['value'] = $result;
				}

				$valid[] = $name;

			} catch (FormValidationException $e) {
				if ($required) {
					$input['error'] = $e->getMessage();
					$invalid[] = $name;
				}
			}
		}

		return array($valid, $invalid);
	}

	public static function set_validator($type, $callback) {
		self::$validators[$type] = $callback;
	}

	public static function set_validation_mapping($mapping, $original) {
		self::$validation_mappings[$mapping] = $original;
	}

	/**
	 * Default validators
	 */

	public static function number_validator($value, $context=null)
	{
		if ($value == (string) (int) $value) {
			return (int) $value;
		} else {
			throw new FormValidationException('Not a number');
		}
	}

	public static function text_validator($value, $context=null)
	{
		return htmlentities((string) $value);
	}

	public static function multiple_choice_validator($value, $context)
	{
		return in_array($value, $context['options']) ||
			   in_array($value, array_flip($context['options']));
	}

	/**
	 * Helpers
	 */

	private function getData()
	{
		return ($this->method === 'get') ? $_GET : $_POST;
	}

	private static function makeId($name)
	{
		return preg_replace('/[^a-z0-9]/', '_', strtolower($name));
	}

}

class FormValidationException extends Exception {}

?>
