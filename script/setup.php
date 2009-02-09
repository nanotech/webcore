<?php
/**
 * Copies base files from webcore/default into app
 */

$base_path = rtrim(dirname(__FILE__), '/script');
$base_url = rtrim(dirname($_SERVER['REQUEST_URI']), '/script').'/';

$defaults = 'webcore/default';
$app = $base_path.'/app';
chdir($base_path);

if(!file_exists('cache')) {
	mkdir('cache');
}

if(!file_exists($base_path.'/app')) {
	mkdir($app);
	mkdir($app.'/plugins');
	copy_r($defaults.'/config', $app.'/config');
	copy_r($defaults.'/views', $app.'/views');
	copy_r($defaults.'/controllers', $app.'/controllers');
	copy($defaults.'/index.php', $base_path.'/index.php');

	$htaccess = file_get_contents($defaults.'/htaccess');
	$htaccess =
		str_replace('RewriteBase "/"', "RewriteBase '$base_url'", $htaccess);

	file_put_contents($base_path.'/.htaccess', $htaccess, FILE_APPEND);

	echo 'app created';

} else {
	echo 'app already exists!';
}

echo "\n";

# from http://php.net/copy
function copy_r($srcdir, $dstdir, $offset = '', $verbose = false)
{
    // A function to copy files from one directory to another one, including subdirectories and
    // nonexisting or newer files. Function returns number of files copied.
    // This function is PHP implementation of Windows xcopy  A:\dir1\* B:\dir2 /D /E /F /H /R /Y
    // Syntaxis: [$returnstring =] dircopy($sourcedirectory, $destinationdirectory [, $offset] [, $verbose]);
    // Example: $num = dircopy('A:\dir1', 'B:\dir2', 1);

    // Original by SkyEye.  Remake by AngelKiha.
    // Linux compatibility by marajax.
    // Offset count added for the possibilty that it somehow miscounts your files.  This is NOT required.
    // Remake returns an explodable string with comma differentiables, in the order of:
    // Number copied files, Number of files which failed to copy, Total size (in bytes) of the copied files,
    // and the files which fail to copy.  Example: 5,2,150000,\SOMEPATH\SOMEFILE.EXT|\SOMEPATH\SOMEOTHERFILE.EXT
    // If you feel adventurous, or have an error reporting system that can log the failed copy files, they can be
    // exploded using the | differentiable, after exploding the result string.
    //
    if(!isset($offset)) $offset=0;
    $num = 0;
    $fail = 0;
    $sizetotal = 0;
    $fifail = '';
    if(!is_dir($dstdir)) mkdir($dstdir);
    if($curdir = opendir($srcdir)) {
        while($file = readdir($curdir)) {
            if($file != '.' && $file != '..') {
                $srcfile = $srcdir . '/' . $file;
                $dstfile = $dstdir . '/' . $file;
                if(is_file($srcfile)) {
                    if(is_file($dstfile)) $ow = filemtime($srcfile) - filemtime($dstfile); else $ow = 1;
                    if($ow > 0) {
                        if($verbose) echo "Copying '$srcfile' to '$dstfile'...<br />";
                        if(copy($srcfile, $dstfile)) {
                            touch($dstfile, filemtime($srcfile)); $num++;
                            chmod($dstfile, 0777);    # added by marajax
                            $sizetotal = ($sizetotal + filesize($dstfile));
                            if($verbose) echo "OK\n";
                        }
                        else {
                            echo "Error: File '$srcfile' could not be copied!<br />\n";
                            $fail++;
                            $fifail = $fifail.$srcfile.'|';
                        }
                    }
                }
                else if(is_dir($srcfile)) {
                    $res = explode(',',$ret);
                    $ret = copy_r($srcfile, $dstfile, $verbose);
                    $mod = explode(',',$ret);
                    $imp = array($res[0] + $mod[0],$mod[1] + $res[1],$mod[2] + $res[2],$mod[3].$res[3]);
                    $ret = implode(',',$imp);
                }
            }
        }
        closedir($curdir);
    }
    $red = explode(',',$ret);
    $ret = ($num + $red[0]).','.(($fail-$offset) + $red[1]).','.($sizetotal + $red[2]).','.$fifail.$red[3];
    return $ret;
}
?>
