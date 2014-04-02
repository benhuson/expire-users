<?php

// @todo Options Page
// @todo Cron job
// @todo Add expired users view
// @todo Add expiring soon view
// @todo Add expired role

class Expire_User_Admin {
	
	var $settings = null;
	
	function Expire_User_Admin() {
		$this->settings = new Expire_User_Settings();
		
		// Profile Fields
		add_action( 'show_user_profile', array( $this, 'extra_user_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'extra_user_profile_fields' ) );
		
		// Save Fields
		add_action( 'personal_options_update', array( $this, 'save_extra_user_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_extra_user_profile_fields' ) );
		
		// Scripts and Styles
		add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		
		// User Columns
		add_filter( 'manage_users_columns', array( $this, 'manage_users_columns' ) );
		add_action( 'manage_users_custom_column', array( $this, 'manage_users_custom_column' ), 10, 3 );
	}
	
	/**
	 * Manage Users Columns
	 */
	function manage_users_columns( $columns ) {
		$columns['expire_user'] = __( 'Expire Date', 'expire-users' );
		return $columns;
	}
	
	/**
	 * Manage Users Custom Column
	 */
	function manage_users_custom_column( $value, $column_name, $user_id ) {
		$user = get_userdata( $user_id );
		$value = '';
		if ( 'expire_user' == $column_name ) {
			$expire_date = get_user_meta( $user_id, '_expire_user_date', true );
			$expired = get_user_meta( $user_id, '_expire_user_expired', true );
			if ( $expired ) {
				$value = date( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), $expire_date );
				if ( $expired == 'Y' ) {
					$value = date( get_option( 'date_format' ), $expire_date );
					$value = '<strong>' . $value . '</strong> <em>' . __( '(expired)', 'expire-users' ) . '</em>';
				}
			}
		}
		return $value;
	}
	
	/**
	 * Check Capabilities
	 */
	function current_expire_user_can( $cap ) {
		switch ( $cap ) {
			case 'expire_users_edit':
				return current_user_can( 'add_users' ) || current_user_can( 'create_users' ) || current_user_can( 'edit_users' ) || current_user_can( 'manage_network_users' ) ? true : false;
		}
		return false;
	}
	
	/**
	 * Save Extra User Profile Fields
	 */
	function save_extra_user_profile_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) )
			return false;
		
		$user = new Expire_User( $user_id );
		$user->set_expire_data( $_POST );
		$user->save_user();
	}
	
	/**
	 * Extra User Profile Fields
	 */
	function extra_user_profile_fields( $user ) {
		$can_edit_profile_expiry = $this->current_expire_user_can( 'expire_users_edit' );
		
		$expire_user = new Expire_User( $user->ID );
		
		// Expire Date Field Values
		$radio_never      = '';
		$radio_date       = '';
		$days_n           = 7;
		$date_in_block    = 'days';
		$expire_timestamp = current_time( 'timestamp' ) + ( 60 * 60 * 24 * 7 );
		$month_n          = '';
		if ( isset( $expire_user->expire_timestamp ) && is_numeric( $expire_user->expire_timestamp ) ) {
			$radio_date = checked( true, true, false );
			$days_n2 = floor( ( $expire_user->expire_timestamp - current_time( 'timestamp' ) ) / 60 / 60 / 24 );
			if ( $days_n2 > 0 ) {
				$days_n = $days_n2;
			}
			$expire_timestamp = $expire_user->expire_timestamp;
			$days_n = ceil( ( $expire_timestamp - current_time( 'timestamp' ) ) / 60 / 60 / 24 );
			if ( $days_n % 7 == 0 ) {
				$days_n = $days_n / 7;
				$date_in_block = 'weeks';
			}
		} else {
			$radio_never = checked( true, true, false );
		}
		$month_n = date( 'm', $expire_timestamp );
		?>
		<h3><?php _e( 'User Expiry Information', 'expire-users' ); ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="address"><?php _e( 'Expire Date', 'expire-users' ); ?></label></th>
				<td>
					<div class="misc-pub-section curtime misc-pub-section-last" style="padding-left:0px;">
						<span id="timestamp"><?php echo $expire_user->get_expire_date_display(); ?></span>
						<?php if ( $can_edit_profile_expiry ) { ?>
							<a href="#delete_user_edit_timestamp" class="delete-user-edit-timestamp hide-if-no-js" tabindex='4'><?php _e( 'Edit', 'expire-users' ) ?></a>
						<?php } ?>
					</div>
					
					<?php if ( $can_edit_profile_expiry ) { ?>
						<fieldset class="expire-user-date-options hide-if-js">
							<legend class="screen-reader-text"><span><?php _e( 'Expiry Date', 'expire-users' ); ?></span></legend>
							<label for="expire_user_date_type_never">
								<input name="expire_user_date_type" type="radio" id="expire_user_date_type_never" value="never" <?php echo $radio_never; ?>>
								<?php _e( 'never', 'expire-users' ); ?>
							</label><br />
							<label for="expire_user_date_type_in">
								<input name="expire_user_date_type" type="radio" id="expire_user_date_type_in" value="in">
								<?php _e( 'In', 'expire-users' ); ?> <input type="text" id="expire_user_date_in_num" name="expire_user_date_in_num" value="<?php echo $days_n; ?>" size="3" maxlength="3" tabindex="4" autocomplete="off">
								<select name="expire_user_date_in_block" id="expire_user_date_in_block">
									<?php echo $this->date_block_menu_options( $date_in_block ); ?>
								</select>
							</label><br />
							<label for="expire_user_date_type_date">
								<input name="expire_user_date_type" type="radio" id="expire_user_date_type_date" value="on" <?php echo $radio_date; ?>>
								<?php _e( 'On', 'expire-users' ); ?> <select id="expire_user_date_on_mm" name="expire_user_date_on_mm" tabindex="4">
									<?php echo $this->month_menu_options( $month_n ); ?>
								</select>
								<input type="text" id="expire_user_date_on_dd" name="expire_user_date_on_dd" value="<?php echo date( 'd', $expire_timestamp ); ?>" size="2" maxlength="2" tabindex="4" autocomplete="off">, 
								<input type="text" id="expire_user_date_on_yyyy" name="expire_user_date_on_yyyy" value="<?php echo date( 'Y', $expire_timestamp ); ?>" size="4" maxlength="4" tabindex="4" autocomplete="off">
								@ <input type="text" id="expire_user_date_on_hrs" name="expire_user_date_on_hrs" value="<?php echo date( 'H', $expire_timestamp ); ?>" size="2" maxlength="2" tabindex="4" autocomplete="off">
								: <input type="text" id="expire_user_date_on_min" name="expire_user_date_on_min" value="<?php echo date( 'i', $expire_timestamp ); ?>" size="2" maxlength="2" tabindex="4" autocomplete="off">
							</label>
						</fieldset>
					<?php } ?>
					
				</td>
			</tr>
			<?php if ( $can_edit_profile_expiry ) { ?>
				<tr>
					<th><label for="role"><?php _e( 'On Expire, Default to Role', 'expire-users' ); ?></label></th>
					<td>
						<select name="expire_user_role" id="expire_user_role">
							<?php
							echo '<option value="">' . __( "Don't change role", 'expire-users' ) . '</option>';
							wp_dropdown_roles( $expire_user->on_expire_default_to_role );
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="postalcode"><?php _e( 'Expire Actions', 'expire-users' ); ?></label></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php _e( 'Expire Actions', 'expire-users' ); ?></span></legend>
							<label for="expire_user_reset_password">
								<input name="expire_user_reset_password" type="checkbox" id="expire_user_reset_password" value="Y" <?php checked( $expire_user->on_expire_user_reset_password ); ?>>
								<?php _e( 'replace user\'s password with a randomly generated one', 'expire-users' ); ?></a>
							</label><br>
							<label for="expire_user_email">
								<input name="expire_user_email" type="checkbox" id="expire_user_email" value="Y" <?php checked( $expire_user->on_expire_user_email ); ?>>
								<?php _e( 'send notification email to user', 'expire-users' ); ?> - <a href="<?php echo admin_url( 'users.php?page=expire_users' ); ?>"><?php _e( 'configure message', 'expire-users' ); ?></a>
							</label><br>
							<label for="expire_user_email_admin">
								<input name="expire_user_email_admin" type="checkbox" id="expire_user_email_admin" value="Y" <?php checked( $expire_user->on_expire_user_email_admin ); ?>>
								<?php _e( 'send notification email to admin', 'expire-users' ); ?> - <a href="<?php echo admin_url( 'users.php?page=expire_users' ); ?>"><?php _e( 'configure message', 'expire-users' ); ?></a>
							</label><br>
							<label for="expire_user_remove_expiry">
								<input name="expire_user_remove_expiry" type="checkbox" id="expire_user_remove_expiry" value="Y" <?php checked( $expire_user->on_expire_user_remove_expiry ); ?>>
								<?php _e( 'remove expiry details and allow user to continue to login', 'expire-users' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				<?php } ?>
		</table>
		<?php
	}
	
	/**
	 * Date Block Menu Options
	 */
	function date_block_menu_options( $selected = '' ) {
		$output = '';
		$blocks = array(
			'days'   => __( 'days', 'expire-users' ),
			'weeks'  => __( 'weeks', 'expire-users' ),
			'months' => __( 'months', 'expire-users' ),
			'years'  => __( 'years', 'expire-users' )
		);
		foreach ( $blocks as $value => $label ) {
			$output .= '<option value="' . $value . '" ' . selected( $selected, $value, false ) . '>' . $label . '</option>';
		}
		return $output;
	}
	
	/**
	 * Month Menu Options
	 */
	function month_menu_options( $selected = '' ) {
		$output = '';
		$months = array(
			'01' => __( 'Jan', 'expire-users' ),
			'02' => __( 'Feb', 'expire-users' ),
			'03' => __( 'Mar', 'expire-users' ),
			'04' => __( 'Apr', 'expire-users' ),
			'05' => __( 'May', 'expire-users' ),
			'06' => __( 'Jun', 'expire-users' ),
			'07' => __( 'Jul', 'expire-users' ),
			'08' => __( 'Aug', 'expire-users' ),
			'09' => __( 'Sep', 'expire-users' ),
			'10' => __( 'Oct', 'expire-users' ),
			'11' => __( 'Nov', 'expire-users' ),
			'12' => __( 'Dec', 'expire-users' )
		);
		foreach ( $months as $value => $label ) {
			$output .= '<option value="' . $value . '" ' . selected( $selected, $value, false ) . '>' . $label . '</option>';
		}
		return $output;
	}
	
	/**
	 * Admin Print Styles
	 */
	function admin_print_styles() {
		$stylesheet_url = plugins_url( 'css/admin.css', dirname( __FILE__ ) );
        $stylesheet_file = WP_PLUGIN_DIR . '/expire-users/css/admin.css';
        if ( file_exists( $stylesheet_file ) ) {
            wp_register_style( 'css-layouts-admin', $stylesheet_url );
            wp_enqueue_style( 'css-layouts-admin' );
        }
	}
	
	/**
	 * Admin Enqueue Scripts
	 */
	function admin_enqueue_scripts() {
		wp_register_script( 'expire-users-admin-user', plugins_url( 'js/admin-user.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0' );
		wp_enqueue_script( 'expire-users-admin-user' );
        wp_localize_script( 'expire-users-admin-user', 'expire_users_admin_user_i18n', array(
        	'cancel' => __( 'Cancel', 'expire-users' ),
        	'edit'   => __( 'Edit', 'expire-users' )
        ) );
	}
	
}
