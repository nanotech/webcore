#!/usr/bin/env php
<?php
/**
 * Copies base files from webcore/default into app
 */

define('IN_TERMINAL', !isset($_SERVER['REQUEST_URI']));
define('WEBCORE_DIR', substr(__FILE__, 0, -strlen('/script/setup.php')));
define('WEBCORE_DEFAULTS', WEBCORE_DIR.'/default');

if (IN_TERMINAL) {
	$appname = $argv[1];
	if (isset($appname) && !empty($appname)) {
		define('BASE_DIR', getcwd().'/'.$appname);
		define('BASE_URI', $argv[2]);
	} else {
		say('Please specify a name for the app.');
		exit;
	}
} else {
	define('BASE_DIR', rtrim(dirname(__FILE__), '/script'));
	define('BASE_URI', rtrim(dirname($_SERVER['REQUEST_URI']), '/script').'/');
	chdir(BASE_DIR);
	say('Please run this script from the command line');
}

define('APP_DIR', BASE_DIR.'/app');

if (!file_exists(BASE_DIR)) {
	mkdir(BASE_DIR);
	mkdir(APP_DIR);
	mkdir(APP_DIR.'/plugins');
	mkdir(APP_DIR.'/cache');
	chmod(APP_DIR.'/cache', 0777);
	say('Copying defaults...');
	copy_defaults('app/config');
	copy_defaults('app/views');
	copy_defaults('app/controllers');
	copy_defaults('static');
	copy_default_and_modify('htaccess', 'RewriteBase /', 'RewriteBase '.BASE_URI.'/', '.htaccess');
	copy_default_and_modify('index.php', 'webcore/run.php', WEBCORE_DIR.'/run.php');
	copy_default_and_modify('app/config/constants.php', "define('BASE_URL', '/')", "define('BASE_URL', '".BASE_URI."/')");

	say('App created.');

} else {
	say('App already exists!');
}

function say($string) {
	echo $string."\n";
}

function copy_defaults($what, $to=BASE_DIR, $from=WEBCORE_DEFAULTS)
{
	$from = $from.'/'.$what;
	$to = $to.'/'.$what;
	copy_r($from, $to);
}

function copy_default_and_modify($what, $find, $replace, $what_new=false, $to=BASE_DIR, $from=WEBCORE_DEFAULTS)
{
	if ($what_new == false) $what_new = $what;
	$file = file_get_contents($from.'/'.$what);
	$file = str_replace($find, $replace, $file);
	file_put_contents($to.'/'.$what_new, $file);
}

/**
 * Copies entire directories recursively,
 * overwriting existing files.
 *
 * Authors: SkyEye, AngelKiha, marajax, NanoTech.
 * Original from <http://php.net/copy>.
 */
function copy_r($srcdir, $dstdir, $ignore_dotfiles=true)
{
	if (!is_dir($srcdir)) {
		return copy($srcdir, $dstdir);
	}

	if (!is_dir($dstdir)) mkdir($dstdir);
	if ($curdir = opendir($srcdir))
	{
		while ($file = readdir($curdir))
		{
			if (($ignore_dotfiles && $file{0} == '.') || $file == '.' || $file == '..') {
				continue;
			}

			$srcfile = $srcdir.'/'.$file;
			$dstfile = $dstdir.'/'.$file;

			if (is_file($srcfile)) {
				if (!copy($srcfile, $dstfile)) {
					echo "Error: File '$srcfile' could not be copied!\n";
				}

			} elseif (is_dir($srcfile)) {
				copy_r($srcfile, $dstfile, $ignore_dotfiles);
			}
		}

		closedir($curdir);
	}

	return true;
}
?>
