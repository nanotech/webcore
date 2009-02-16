<?php
/**
 * This is a layout. Technically, a layout isn't any different from any other
 * view -- except that it uses a special "Filter". Filters are comparable to
 * template engines in other frameworks, but more powerful. Rather than just
 * having one view processer, you can have many, chained together.
 *
 * Have a look at `app/config/setup.php` for a more detailed look at filters.
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Welcome to WebCore</title>
		<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL;?>/static/theme/style.css" />
	</head>
	<body>
		<div id="content">
			<?php echo $the_content; ?>
		</div>
	</body>
</html>
