<?php
/*
Plugin Name: MyOpenID
Plugin URI: https://nikolay.com/projects/wordpress/myopenid/
Description: MyOpenID plugin allows you to use your blog as your OpenID identity by delegating it to an external provider.
Version: 0.4
Author: Nikolay Kolev
Author URI: https://nikolay.com/
*/

define('MYOPENID_NONCE', 'myopenid_nonce');

define('MYOPENID_USERNAME_OPTION', 'myopenid_username');
define('MYOPENID_PROVIDER_OPTION', 'myopenid_provider');

function myopenid_reset_settings() {
	delete_option(MYOPENID_PROVIDER_OPTION);
	delete_option(MYOPENID_USERNAME_OPTION);
}

function myopenid_has_settings() {
	$provider = get_option(MYOPENID_PROVIDER_OPTION);
	$username = get_option(MYOPENID_USERNAME_OPTION);
	return !empty($provider) && !empty($username);
}

add_action('init', 'myopenid_init');

function myopenid_init() {
	add_action('admin_menu', 'myopenid_admin_menu');
	add_action('wp_head', 'myopenid_head');
}

function myopenid_admin_menu() {
	add_options_page(__('MyOpenID Settings'), __('MyOpenID'), 8, __FILE__, 'myopenid_settings');
}

function myopenid_settings() {
	if (isset($_POST['submit'])) {
		if (!current_user_can('manage_options')) {
			die(__('Unauthorized access!'));
		}
		check_admin_referer(MYOPENID_NONCE);
		if (isset($_POST['myopenid_provider'])) {
			$provider = $_POST['myopenid_provider'];
			if (empty($provider)) {
				delete_option(MYOPENID_PROVIDER_OPTION);
			} else {
				update_option(MYOPENID_PROVIDER_OPTION, $provider);
			}
		}
		if (isset($_POST['myopenid_username'])) {
			$username = $_POST['myopenid_username'];
			if (empty($username)) {
				delete_option(MYOPENID_USERNAME_OPTION);
			} else {
				update_option(MYOPENID_USERNAME_OPTION, $username);
			}
		}
?>
<div id="myopenid_warning" class="updated fade">
	<p><strong><?php _e('MyOpenID:'); ?></strong> <?php _e('Settings saved!'); ?></p>
</div>
<?php
	}

	$provider = get_option(MYOPENID_PROVIDER_OPTION);
	$username = get_option(MYOPENID_USERNAME_OPTION);

?>
<div class="wrap">
	<h2><?php _e('MyOpenID Settings'); ?></h2>
	<div id="poststuff" class="metabox-holder">
		<form name="form0" method="post" action=""><?php wp_nonce_field(MYOPENID_NONCE); ?>
			<div class="postbox open">
				<h3 class="hndle"><?php _e('OpenID Provider Info'); ?></h3>
				<div class="inside">
					<table class="form-table">
						<tr>
							<th><label for="myopenid_provider"><?php _e('Provider:'); ?></label></th>
							<td><select id="myopenid_provider" name="myopenid_provider">
								<?php if (empty($provider)) { ?><option></option><?php } ?>
								<option value="pip"<?php if ($provider == 'pip') echo ' selected="selected"'; ?>>Symantec PIP</option>
								<option value="claimid"<?php if ($provider == 'claimid') echo ' selected="selected"'; ?>>claimID</option>
							</select></td>
						</tr>
						<tr>
							<th><label for="myopenid_username"><?php _e('Username:'); ?></label></th>
							<td><input type="text" id="myopenid_username" name="myopenid_username" value="<?php echo $username; ?>" /></td>
						</tr>
					</table>
				</div>
			</div>
			<p class="submit">
				<input type="submit" name="submit" value="<?php _e('Save Settings'); ?>" class="button-primary" />
			</p>
		</form>	
	</div>
</div>
<?php
}

if (!myopenid_has_settings() && !isset($_POST['submit'])) {
	add_action('admin_notices', 'myopenid_admin_notices');
}

function myopenid_admin_notices() {
?>
<div id="myopenid_warning" class="updated fade">
	<p><strong><?php _e('MyOpenID:'); ?></strong> <?php _e('You need to configure the plugin in order to start utilizing it!'); ?></p>
</div>
<?php
}

function myopenid_head() {
	if (myopenid_has_settings()) {
		$provider = get_option(MYOPENID_PROVIDER_OPTION);
		$username = get_option(MYOPENID_USERNAME_OPTION);
?>
<!-- MyOpenID Plugin - Start -->
<?php
		switch ($provider) {
			case "pip":
?>
<link rel="openid.server" href="http://pip.verisignlabs.com/server" />
<link rel="openid.delegate" href="http://<?php echo $username; ?>.pip.verisignlabs.com" />
<link rel="openid2.provider" href="http://pip.verisignlabs.com/server" />
<link rel="openid2.local_id" href="http://<?php echo $username; ?>.pip.verisignlabs.com" />
<meta http-equiv="X-XRDS-Location" content="http://pip.verisignlabs.com/user/<?php echo $username; ?>/yadisxrds" />
<meta http-equiv="X-YADIS-Location" content="http://pip.verisignlabs.com/user/<?php echo $username; ?>/yadisxrds" />
<?php
				break;
			case "claimid":
?>
<link rel="openid.server" href="http://openid.claimid.com/server" />
<link rel="openid.delegate" href="http://openid.claimid.com/<?php echo $username; ?>" />
<?php
				break;
		}
?>
<!-- MyOpenID Plugin - End -->
<?php
	}
}

?>