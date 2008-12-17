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
    mkdir($app.'/config');
    mkdir($app.'/controllers');
    mkdir($app.'/views');
    copy($defaults.'/config/init.php', $app.'/config/init.php');
    copy($defaults.'/config/constants.php', $app.'/config/constants.php');
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
?>
