<?php
function wpbb_admin_menu ()
{
	add_options_page('CruelDrool Bridge!', 'CruelDrool Bridge!', 'administrator', 'wpbb-admin', 'wpbb_display_options');
}

function wpbb_display_options() {
	$submit = false;
	$active = trim(get_option('wpbb_active'));
	$path = trim(get_option('wpbb_path'));
	$url = trim(get_option('wpbb_url'));
	$grp_cap = get_option('wpbb_grp_cap', Array());
	
	if (isset($_POST['action']) && ($_POST['action'] == 'wpbb_update'))
	{
		$submit = true;
		$active = trim($_POST['wpbb_active']);
		$path = trim($_POST['wpbb_path']);
		$url = trim($_POST['wpbb_url']);
		(isset($_POST['grp_cap'])) ? $grp_cap = $_POST['grp_cap']:'';
	}

	if ($active == "")
	{
		$active = "no";
	}

	if ($path == "")
	{
		$path = dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/phpbb3';
	}

	if ($url == "")
	{
		$url = 'http://' . $_SERVER['HTTP_HOST'] . '/phpbb3';
	}

	if (!file_exists($path))
	{
		$active = "no";
		?><div id="message" class="updated fade"><p><?php _e('Unable to find config.php. Cannot activate bridge.') ?></p></div><?php
	}
	update_option('wpbb_grp_cap', $grp_cap);
	update_option('wpbb_active', $active);
	update_option('wpbb_path', $path);
	update_option('wpbb_url', $url);

	if ($submit)
	{
		?><div id="message" class="updated fade"><p><?php _e('Options saved.') ?></p></div><?php
	}

?><div class="wrap">
	<form method="post" action="">
		<h2>CruelDrool Bridge!</h2>
		<table class="form-table">
			<tr>
				<th><label><?php _e('Path to phpBB:') ?></label></th>
				<td><input type="text" name="wpbb_path" class="regular-text" value="<?php echo $path; ?>" /></td>
			</tr>
			<tr>
				<th><label><?php _e('URL to phpBB:') ?></label></th>
				<td><input type="text" name="wpbb_url" class="regular-text" value="<?php echo $url; ?>" /></td>
			</tr>
			<tr>
				<th><label><?php _e('Activate Bridge:') ?></label></th>
				<td>
					<input name="wpbb_active" type="radio" id="active_yes" value="yes" <?php
						if ($active == "yes")
						{
							echo 'checked="checked" ';
						}
					?>/> <?php _e('Yes') ?>
					<br />
					<input name="wpbb_active" type="radio" id="active_no" value="no" <?php
						if ($active != "yes")
						{
							echo 'checked="checked" ';
						}
					?>/> <?php _e('No') ?>
				</td>
			</tr>
			<?php if (function_exists('wpbb_get_groups') and $active == "yes") : ?>
			<tr>
				<th>
					<label>Assign capabilites to groups</label>
					<p class="description">Members of these groups will receive the capabilities set here. Members of multiple groups get their capability from the group with the highest one.</p>
					<p class="description">NB! Users that are founders are always administrators on Wordpress.</p>
				</th>
				<td>
				<?php
					global $user;

					$available_capabilities = wpbb_get_available_capabilities();

					$groups = wpbb_get_groups();
					foreach ($groups as $id => $group) {
						$name =(($user->lang['G_'.$group['name']]) ? $user->lang['G_'.$group['name']] : $group['name'] );
						echo '<select name="grp_cap['.$id.']">'."\n";
						foreach ($available_capabilities as $capability => $rank) {
							echo '<option value"'. $capability .'"'.(($grp_cap[$id]==$capability || (!isset($grp_cap[$id]) && $capability == 'subscriber') )? ' selected="selected"' : '' ).'>'. $capability .'</option>'."\n";
						}
						echo '</select>'."\n";
						echo '<label'.( ($group['colour']) ? ' style="color:#'.$group['colour'].'"' : '' ) .'>'.$name.'</label><br/>';
					}
				?>
				</td>
			</tr>
		<?php endif; ?>
		</table>
		<p class="submit">
			<input type="hidden" name="action" value="wpbb_update" />
			<input type="submit" class="button button-primary" name="Submit" value="<?php _e('Update Options') ?>" />
		</p>
</div><?php
}
?>