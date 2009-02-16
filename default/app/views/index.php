<?php
/**
 * Hello! We're going to try a rather different style of tutorial.
 * Instead of switching between a separate guide, and the source code,
 * we'll work directly _in_ the source code with comments.
 *
 * Below is a rather standard HTML document, with a bit of PHP thrown
 * in. You may be wondering where the $file variable is from, or where
 * the HTML boilerplate code (doctype, head, etc) is.
 *
 * WebCore uses an MVC format, and this is a view. $file comes from
 * the controller, `app/controllers/Default.php`. The HTML wrapping is
 * also a kind of view, a layout. It's in `app/views/layouts/main.php`.
 *
 * Now that you know a bit about WebCore, go have a look at the files
 * I've mentioned. There'll be more instruction there.
 */
?>
<h1><span>Welcome to</span> WebCore</h1>
<p>
	Get started by editing this page, <code><?php echo $file; ?></code>.
</p>
