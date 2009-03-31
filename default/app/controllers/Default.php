<?php
/**
 * This is a controller, where most of the logic takes place. It pulls
 * information from somewhere (usually a database), processes it, and
 * "exports" it to the view.
 */
class DefaultController extends Controller {

	/**
	 * A url is mapped to a controller and a method by the Director, in the
	 * router file, `app/config/router.php`. This is the method for the index,
	 * and exports the $file variable you saw in the index view.
	 */
	public function index()
	{
		# WebCore has a concept of "resources". Rather than dealing directly
		# with files, you simply refer to resource names.
		#
		# Resources can be categorized into "types", to prevent name
		# conflicts. PHP classes are placed in the "php" type, which
		# is the default, and views are placed in "views".
		#
		# Normally, you don't look for resources manually, as it is done for
		# you by the various WebCore classes, but since I wanted to get the
		# filename of the index view, Core::find_resource is run here.
		#
		$file = Core::find_resource('index', 'views');

		# Some tweaks to the file's path to keep it short
		# and unambiguous for the page.
		$file = substr($file, strlen(APP_DIR));
		$file = 'app'.$file;

		# Exports the $file variable to the view.
		$this->export('file', $file);

		# Renders the "index" view and displays it.
		$this->render('index');
	}

	/**
	 * This method handles errors, such as 404s.
	 */
	public function error() {
		header('HTTP/1.0 404 Not Found');
		$this->render('error');
	}
}
?>
