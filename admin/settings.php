<?php

class Expire_User_Settings {

	public function __construct() {
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
			'expire_user_role'             => '',
			'expire_user_reset_password'   => 'N',
			'expire_user_email'            => 'N',
			'expire_user_email_admin'      => 'N',
			'expire_user_remove_expiry'    => 'N',
			'expire_user_date_type'        => 'never',
			'expire_user_date_in_num'      => 10,
			'expire_user_date_in_block'    => 'days',
			'expire_timestamp'             => current_time( 'timestamp' ),
			'auto_expire_registered_users' => 'N'
		);
		$settings = wp_parse_args( get_option( 'expire_users_default_expire_settings', $default_settings ), $default_settings );
		return $settings;
	}

	/**
	 * Options Page
	 */
	function options_page() {
		global $expire_users, $wp_version;
		if ( ! isset( $_REQUEST['updated'] ) ) {
			$_REQUEST['updated'] = false;
		}
		?>

		<div class="wrap">
			<?php
			$tag = version_compare( $wp_version, '4.3', '<' ) ? 'h2' : 'h1';
			echo '<' . $tag . '>' . esc_html__( 'Expire Users Settings', 'expire-users' ) . '</' . $tag . '>';
			?>

			<?php if ( false !== $_REQUEST['updated'] ) : ?>
				<div><p><strong><?php esc_html_e( 'Options saved', 'expire-users' ); ?></strong></p></div>
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

				<h3><?php esc_html_e( 'Registered User Expiry Settings', 'expire-users' ); ?></h3>
				<p>
					<label for="expire_user_auto_expire_registered_users">
						<input name="expire_users_default_expire_settings[auto_expire_registered_users]" type="checkbox" id="expire_user_auto_expire_registered_users" value="Y" <?php checked( 'Y', $expire_settings['auto_expire_registered_users'] ); ?>>
						<?php esc_html_e( 'Automatically set expiry date for new users who register via the main WordPress registration form.', 'expire-users' ); ?>
					</label>
				</p>

				<table class="form-table expire_user_auto_expire_registered_users_toggle">
					<tr valign="top">
						<th scope="row"><label for="expire_user_date_type_never"><?php esc_html_e( 'Expiry Date', 'expire-users' ); ?></label></th>
						<td>
							<fieldset class="expire-user-date-options hide-if-js" style="display: block; ">
								<legend class="screen-reader-text"><span><?php esc_html_e( 'Expiry Date', 'expire-users' ); ?></span></legend>
								<label for="expire_user_date_type_never">
									<input name="expire_users_default_expire_settings[expire_user_date_type]" type="radio" id="expire_user_date_type_never" value="never" <?php checked( 'never', $expire_settings['expire_user_date_type'] ); ?>>
									<?php echo esc_html_x( 'Never', 'expire date type', 'expire-users' ); ?>
								</label><br>
								<label for="expire_user_date_type_in">
									<input name="expire_users_default_expire_settings[expire_user_date_type]" type="radio" id="expire_user_date_type_in" value="in" <?php checked( 'in', $expire_settings['expire_user_date_type'] ); ?>>
									<?php echo esc_html_x( 'In', 'expire date type', 'expire-users' ); ?> <input type="text" id="expire_user_date_in_num" name="expire_users_default_expire_settings[expire_user_date_in_num]" value="<?php echo esc_attr( $expire_settings['expire_user_date_in_num'] ); ?>" size="3" maxlength="3" tabindex="4" autocomplete="off">
									<select name="expire_users_default_expire_settings[expire_user_date_in_block]" id="expire_user_date_in_block">
										<?php echo $expire_users->admin->date_block_menu_options( $expire_settings['expire_user_date_in_block'] ); ?>
									</select>
								</label><br>
								<label for="expire_user_date_type_date">
									<input name="expire_users_default_expire_settings[expire_user_date_type]" type="radio" id="expire_user_date_type_date" value="on" <?php checked( 'on', $expire_settings['expire_user_date_type'] ); ?>>
									<?php echo esc_html_x( 'On', 'expire date type', 'expire-users' ); ?> <select id="expire_users_default_expire_settings_expire_timestamp_mm" name="expire_users_default_expire_settings[expire_timestamp][mm]" tabindex="4">
										<?php echo $expire_users->admin->month_menu_options( $month_n ); ?>
									</select>
									<input type="text" id="expire_users_default_expire_settings_expire_timestamp_dd" name="expire_users_default_expire_settings[expire_timestamp][dd]" value="<?php echo esc_attr( date( 'd', $expire_timestamp ) ); ?>" size="2" maxlength="2" tabindex="4" autocomplete="off">, 
									<input type="text" id="expire_users_default_expire_settings_expire_timestamp_yyyy" name="expire_users_default_expire_settings[expire_timestamp][yyyy]" value="<?php echo esc_attr( date( 'Y', $expire_timestamp ) ); ?>" size="4" maxlength="4" tabindex="4" autocomplete="off">
									@ <input type="text" id="expire_users_default_expire_settings_expire_timestamp_hrs" name="expire_users_default_expire_settings[expire_timestamp][hrs]" value="<?php echo esc_attr( date( 'H', $expire_timestamp ) ); ?>" size="2" maxlength="2" tabindex="4" autocomplete="off">
									: <input type="text" id="expire_users_default_expire_settings_expire_timestamp_min" name="expire_users_default_expire_settings[expire_timestamp][min]" value="<?php echo esc_attr( date( 'i', $expire_timestamp ) ); ?>" size="2" maxlength="2" tabindex="4" autocomplete="off">
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th><label for="expire_user_role"><?php esc_html_e( 'On Expire, Default to Role', 'expire-users' ); ?></label></th>
						<td>
							<select name="expire_users_default_expire_settings[expire_user_role]" id="expire_user_role">
								<option value="" <?php selected( '', $expire_settings['expire_user_role'] ); ?>><?php esc_html_e( 'Don\'t change role', 'expire-users' ); ?></option>
								<?php wp_dropdown_roles( $expire_settings['expire_user_role'] ); ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="postalcode"><?php esc_html_e( 'Expire Actions', 'expire-users' ); ?></label></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php esc_html_e( 'Expire Actions', 'expire-users' ); ?></span></legend>
								<label for="expire_user_reset_password">
									<input name="expire_users_default_expire_settings[expire_user_reset_password]" type="checkbox" id="expire_user_reset_password" value="Y" <?php checked( 'Y', $expire_settings['expire_user_reset_password'] ); ?>>
									<?php esc_html_e( 'Replace user\'s password with a randomly generated one', 'expire-users' ); ?>
								</label><br>
								<label for="expire_user_remove_expiry">
									<input name="expire_users_default_expire_settings[expire_user_remove_expiry]" type="checkbox" id="expire_user_remove_expiry" value="Y" <?php checked( 'Y', $expire_settings['expire_user_remove_expiry'] ); ?>>
									<?php esc_html_e( 'Remove expiry details and allow user to continue to login', 'expire-users' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th><label><?php esc_html_e( 'Email Notifications', 'expire-users' ); ?></label></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php esc_html_e( 'Email Notifications', 'expire-users' ); ?></span></legend>
								<?php
								$notifications = Expire_User_Notifications_Admin::get_notifications();
								foreach ( $notifications as $notification ) {
									$checked = '';
									$name = $notification['name'];
									if ( 'expire_users_notification_message' == $name ) {
										$name = 'expire_user_email';
										$checked = checked( 'Y', $expire_settings['expire_user_email'], false );
									} elseif ( 'expire_users_notification_admin_message' == $name ) {
										$name = 'expire_user_email_admin';
										$checked = checked( 'Y', $expire_settings['expire_user_email_admin'], false );
									}
									?>
									<label for="<?php echo esc_attr( $name ); ?>" title="<?php echo esc_attr( $notification['description'] ); ?>">
										<input name="expire_users_default_expire_settings[<?php echo esc_attr( $name ); ?>]" type="checkbox" id="<?php echo esc_attr( $name ); ?>" value="Y"<?php echo $checked; ?> />
										<?php echo esc_html( $notification['notification'] ); ?>
									</label><br />
									<?php
								}
								?>
							</fieldset>
						</td>
					</tr>
				</table>

				<h3><?php esc_html_e( 'Notification Emails', 'expire-users' ); ?></h3>
				<p><?php esc_html_e( 'These emails are sent if you have checked the checkboxes on a user\'s profile.', 'expire-users' ); ?><br />
					<?php esc_html_e( 'You may use the following placeholders in the notification email messages below:', 'expire-users' ); ?></p>
				<p><code>%%expirydate%%</code> <code>%%username%%</code> <code>%%name%%</code> <code>%%sitename%%</code></p>
				<?php Expire_User_Notifications_Admin::admin_table(); ?>

				<p class="submit"><input type="submit" value="<?php esc_attr_e( 'Save Options', 'expire-users' ); ?>" class="button button-primary" /></p>

			</form>

		</div>

		<?php
	}

}
