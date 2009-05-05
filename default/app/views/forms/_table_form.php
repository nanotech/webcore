<form action="<?php echo $action;?>" method="<?php echo $method;?>"<?php if ($id) echo ' id="'.$id.'"';?>>
	<table>
		<?php 
		$hidden_inputs = '';

		foreach ($inputs as $title => $opts):
			if ($opts['html_type'] == 'hidden') {
				$hidden_inputs .= '<input type="'.$opts['html_type'].'" '.html_attrs($opts, $exclude_attrs)." />\n";
				continue;
			}
		?>
		<tr>
			<th><?php echo $title;?></th>
			<td>
				<?php if ($opts['html_type'] == 'select'):?>
				<select <?php echo html_attrs($opts, $exclude_attrs);?>>
					<?php foreach ($opts['options'] as $name => $value):?>
					<option value="<?php echo $value;?>"><?php echo $name;?></option>
					<?php endforeach;?>
				</select>
				<?php elseif ($opts['html_type'] == 'radio'):?>
					<?php foreach ($opts['options'] as $name => $value):?>
					<?php $id = underscore($name);?>
					<input type="<?php echo $opts['html_type'];?>" id="<?php echo $id;?>" value="<?php echo $value;?>" <?php echo html_attrs($opts, array_merge($exclude_attrs, array('id', 'value')));?> />
					<label for="<?php echo $id;?>"><?php echo $name;?></label>
					<?php endforeach;?>
				<?php else:?>
				<input type="<?php echo $opts['html_type'];?>" <?php echo html_attrs($opts, $exclude_attrs);?> />
				<?php endif;?>
			</td>
		</tr>
		<?php endforeach;?>

		<tr class="submit">
			<td colspan="2">
				<?php echo $hidden_inputs;?>
				<button type="submit"><?php echo $submit_button_text;?></button>
			</td>
		</tr>
	</table>
</form>
