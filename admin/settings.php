<?php

class Expire_User_Settings {
	
	function Expire_User_Settings() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'options_page_item' ) );
	}
	
	/**
	 * Create Options Page Item
	 */
	function options_page_item() {
		add_users_page( __( 'Expire Settings', 'expire-users' ), __( 'Expire Settings', 'expire-users' ), 'manage_options', 'expire_users', array( $this, 'options_page' ) );
	}
	
	/**
	 * Register Settings
	 */
	function register_settings() {
    	register_setting( 'expire_users_options_group', 'expire_users_default_expire_settings', array( $this, 'validate_expire_settings' ) );
    	register_setting( 'expire_users_options_group', 'expire_users_notification_message', array( $this, 'validate_email_message' ) );
    	register_setting( 'expire_users_options_group', 'expire_users_notification_admin_message', array( $this, 'validate_email_message' ) );
	}
	
	/**
	 * Validate Expire Settings
	 */
	function validate_expire_settings( $input ) {
		$defaults = wp_parse_args( $input['expire_timestamp'], array(
			'mm'   => 0,
			'dd'   => 0,
			'yyyy' => 0,
			'hrs'  => 0,
			'min'  => 0,
		) );
		$input['expire_timestamp'] = mktime( $defaults['hrs'], $defaults['min'], 0, $defaults['mm'], $defaults['dd'], $defaults['yyyy'] );
		return $input;
	}
	
	/**
	 * Validate Email Message
	 * Strips out HTML and scripts.
	 */
	function validate_email_message( $input ) {
		return wp_kses( $input, array() );
	}
	
	/**
	 * Get Default Expire Settings
	 */
	function get_default_expire_settings() {
		$default_settings = array(
			'expire_user_role'           => '',
			'expire_user_reset_password' => 'N',
			'expire_user_email'          => 'N',
			'expire_user_email_admin'    => 'N',
			'expire_user_date_type'      => 'never',
			'expire_user_date_in_num'    => 10,
			'expire_user_date_in_block'  => 'days',
			'expire_timestamp'           => time()
		);
		$settings = wp_parse_args( get_option( 'expire_users_default_expire_settings', $default_settings ), $default_settings );
		return $settings;
	}
	
	/**
	 * Options Page
	 */
	function options_page() {
		if ( ! isset( $_REQUEST['updated'] ) )
			$_REQUEST['updated'] = false; 
		?>
	 
		<div class="wrap">
			<?php
			screen_icon();
			echo '<h2>' . __( 'Expire Users Settings', 'expire-users' ) . '</h2>';
			?>
			
			<?php if ( false !== $_REQUEST['updated'] ) : ?>
				<div><p><strong><?php _e( 'Options saved' ); ?></strong></p></div>
			<?php endif; ?>
			
			<form method="post" action="options.php">
				
				<?php
				$expire_settings = $this->get_default_expire_settings();
				$notification_message = get_option( 'expire_users_notification_message' );
				$notification_admin_message = get_option( 'expire_users_notification_admin_message' );
				$expire_timestamp = $expire_settings['expire_timestamp'];
				$month_n = date( 'm', $expire_settings['expire_timestamp'] );
				?>
				
				<?php settings_fields( 'expire_users_options_group' ); ?>
				
				<!--
				<h3><?php _e( 'Default Settings for New Users', 'expire-users' ); ?></h3>
				<p><?php _e( 'These expiry settings are set when a new user registers or is created.', 'expire-users' ); ?></p>
	 
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="expire_user_date_type_never"><?php _e( 'Expiry Date', 'expire-users' ); ?></label></th>
						<td>
							<fieldset class="expire-user-date-options hide-if-js" style="display: block; ">
								<legend class="screen-reader-text"><span><?php _e( 'Expiry Date', 'expire-users' ); ?></span></legend>
								<label for="expire_user_date_type_never">
									<input name="expire_users_default_expire_settings[expire_user_date_type]" type="radio" id="expire_user_date_type_never" value="never" <?php checked( 'never', $expire_settings['expire_user_date_type'] ); ?>>
									<?php _e( 'never', 'expire date type', 'expire-users' ); ?>
								</label><br>
								<label for="expire_user_date_type_in">
									<input name="expire_users_default_expire_settings[expire_user_date_type]" type="radio" id="expire_user_date_type_in" value="in" <?php checked( 'in', $expire_settings['expire_user_date_type'] ); ?>>
									<?php _ex( 'In', 'expire date type', 'expire-users' ); ?> <input type="text" id="expire_user_date_in_num" name="expire_users_default_expire_settings[expire_user_date_in_num]" value="<?php echo $expire_settings['expire_user_date_in_num']; ?>" size="3" maxlength="3" tabindex="4" autocomplete="off">
									<select name="expire_users_default_expire_settings[expire_user_date_in_block]" id="expire_user_date_in_block">
										<option value="days" <?php selected( 'days', $expire_settings['expire_user_date_in_block'] ); ?>><?php _e( 'days', 'expire-users' ); ?></option>
										<option value="weeks" <?php selected( 'weeks', $expire_settings['expire_user_date_in_block'] ); ?>><?php _e( 'weeks', 'expire-users' ); ?></option>
										<option value="months" <?php selected( 'months', $expire_settings['expire_user_date_in_block'] ); ?>><?php _e( 'months', 'expire-users' ); ?></option>
										<option value="years" <?php selected( 'years', $expire_settings['expire_user_date_in_block'] ); ?>><?php _e( 'years', 'expire-users' ); ?></option>
									</select>
								</label><br>
								<label for="expire_user_date_type_date">
									<input name="expire_users_default_expire_settings[expire_user_date_type]" type="radio" id="expire_user_date_type_date" value="on" <?php checked( 'on', $expire_settings['expire_user_date_type'] ); ?>>
									<?php _e( 'On', 'expire date type', 'expire-users' ); ?> <select id="expire_users_default_expire_settings_expire_timestamp_mm" name="expire_users_default_expire_settings[expire_timestamp][mm]" tabindex="4">
										<option value="01" <?php selected( $month_n, '01' ); ?>><?php _e( 'Jan', 'expire-users' ); ?></option>
										<option value="02" <?php selected( $month_n, '02' ); ?>><?php _e( 'Feb', 'expire-users' ); ?></option>
										<option value="03" <?php selected( $month_n, '03' ); ?>><?php _e( 'Mar', 'expire-users' ); ?></option>
										<option value="04" <?php selected( $month_n, '04' ); ?>><?php _e( 'Apr', 'expire-users' ); ?></option>
										<option value="05" <?php selected( $month_n, '05' ); ?>><?php _e( 'May', 'expire-users' ); ?></option>
										<option value="06" <?php selected( $month_n, '06' ); ?>><?php _e( 'Jun', 'expire-users' ); ?></option>
										<option value="07" <?php selected( $month_n, '07' ); ?>><?php _e( 'Jul', 'expire-users' ); ?></option>
										<option value="08" <?php selected( $month_n, '08' ); ?>><?php _e( 'Aug', 'expire-users' ); ?></option>
										<option value="09" <?php selected( $month_n, '09' ); ?>><?php _e( 'Sep', 'expire-users' ); ?></option>
										<option value="10" <?php selected( $month_n, '10' ); ?>><?php _e( 'Oct', 'expire-users' ); ?></option>
										<option value="11" <?php selected( $month_n, '11' ); ?>><?php _e( 'Nov', 'expire-users' ); ?></option>
										<option value="12" <?php selected( $month_n, '12' ); ?>><?php _e( 'Dec', 'expire-users' ); ?></option>
									</select>
									<input type="text" id="expire_users_default_expire_settings_expire_timestamp_dd" name="expire_users_default_expire_settings[expire_timestamp][dd]" value="<?php echo date( 'd', $expire_timestamp ); ?>" size="2" maxlength="2" tabindex="4" autocomplete="off">, 
									<input type="text" id="expire_users_default_expire_settings_expire_timestamp_yyyy" name="expire_users_default_expire_settings[expire_timestamp][yyyy]" value="<?php echo date( 'Y', $expire_timestamp ); ?>" size="4" maxlength="4" tabindex="4" autocomplete="off">
									@ <input type="text" id="expire_users_default_expire_settings_expire_timestamp_hrs" name="expire_users_default_expire_settings[expire_timestamp][hrs]" value="<?php echo date( 'H', $expire_timestamp ); ?>" size="2" maxlength="2" tabindex="4" autocomplete="off">
									: <input type="text" id="expire_users_default_expire_settings_expire_timestamp_min" name="expire_users_default_expire_settings[expire_timestamp][min]" value="<?php echo date( 'i', $expire_timestamp ); ?>" size="2" maxlength="2" tabindex="4" autocomplete="off">
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th><label for="expire_user_role"><?php _e( 'On Expire, Default to Role', 'expire-users' ); ?></label></th>
						<td>
							<select name="expire_users_default_expire_settings[expire_user_role]" id="expire_user_role">
								<option value="" <?php selected( '', $expire_settings['expire_user_role'] ); ?>><?php _e( 'Don\'t change role', 'expire-users' ); ?></option>
								<option value="editor" <?php selected( 'editor', $expire_settings['expire_user_role'] ); ?>><?php _e( 'Editor', 'expire-users' ); ?></option>
								<option value="administrator" <?php selected( 'administrator', $expire_settings['expire_user_role'] ); ?>><?php _e( 'Administrator', 'expire-users' ); ?></option>
								<option value="author" <?php selected( 'author', $expire_settings['expire_user_role'] ); ?>><?php _e( 'Author', 'expire-users' ); ?></option>
								<option value="contributor" <?php selected( 'contributor', $expire_settings['expire_user_role'] ); ?>><?php _e( 'Contributor', 'expire-users' ); ?></option>
								<option value="subscriber" <?php selected( 'subscriber', $expire_settings['expire_user_role'] ); ?>><?php _e( 'Subscriber', 'expire-users' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="postalcode"><?php _e( 'Expire Actions', 'expire-users' ); ?></label></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e( 'Expire Actions', 'expire-users' ); ?></span></legend>
								<label for="expire_user_reset_password">
									<input name="expire_users_default_expire_settings[expire_user_reset_password]" type="checkbox" id="expire_user_reset_password" value="Y" <?php checked( 'Y', $expire_settings['expire_user_reset_password'] ); ?>>
									<?php _e( 'replace user\'s password with a randomly generated one', 'expire-users' ); ?>
								</label><br>
								<label for="expire_user_email">
									<input name="expire_users_default_expire_settings[expire_user_email]" type="checkbox" id="expire_user_email" value="Y" <?php checked( 'Y', $expire_settings['expire_user_email'] ); ?>>
									<?php _e( 'send notification email to user', 'expire-users' ); ?>
								</label><br>
								<label for="expire_user_email_admin">
									<input name="expire_users_default_expire_settings[expire_user_email_admin]" type="checkbox" id="expire_user_email_admin" value="Y" <?php checked( 'Y', $expire_settings['expire_user_email_admin'] ); ?>>
									<?php _e( 'send notification email to admin', 'expire-users' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
				</table>
				-->
				
				<h3><?php _e( 'Notification Emails', 'expire-users' ); ?></h3>
				<p><?php _e( 'These emails are sent if you have checked the checkboxes on a user\'s profile.', 'expire-users' ); ?><br />
					<?php _e( 'You may use the following placeholders in the notification email messages below:', 'expire-users' ); ?></p>
				<p><code>%%expirydate%%</code> <code>%%username%%</code> <code>%%name%%</code> <code>%%sitename%%</code></p>
	 
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="expire_users_notification_message"><?php _e( 'User Notification Email', 'expire-users' ); ?></label></th>
						<td><p><textarea id="expire_users_notification_message" name="expire_users_notification_message" rows="5" cols="50" class="large-text code"><?php echo $notification_message; ?></textarea></p></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="expire_users_notification_admin_message"><?php _e( 'Admin Notification Email', 'expire-users' ); ?></label></th>
						<td><p><textarea id="expire_users_notification_admin_message" name="expire_users_notification_admin_message" rows="5" cols="50" class="large-text code"><?php echo $notification_admin_message; ?></textarea></p></td>
					</tr>
				</table>
			 
				<p class="submit"><input type="submit" value="<?php _e( 'Save Options' ); ?>" class="button-primary" /></p>
			 
			</form>
	 
		</div>
	 
		<?php
	}
	
}

?>