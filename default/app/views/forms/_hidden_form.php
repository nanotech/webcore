<h1>Processsing...</h1>
<form action="<?php echo $action;?>" method="<?php echo $method;?>" id="<?php echo ($id) ? $id : 'hidden-form';?>" class="hidden-form">
	<?php 
	$hidden_inputs = '';

	foreach ($inputs as $title => $opts) {
		if ($opts['html_type'] == 'hidden') {
			echo '<input type="'.$opts['html_type'].'" '.html_attrs($opts, $exclude_attrs)." />\n";
			continue;
		}
	}
	?>

	<p>If you are not redirected in a few seconds, <button type="submit">click here</button>.</p>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(function($) { $('#<?php echo ($id) ? $id : 'hidden-form';?>').submit(); });
/* ]]> */
</script>
