<?php

// @todo Options Page
// @todo Cron job
// @todo Add expired users view
// @todo Add expiring soon view
// @todo Add expired role

class Expire_User_Admin {

	var $settings = null;

	public function __construct() {
		$this->settings = new Expire_User_Settings();

		// Admin Actions
		add_action( 'admin_init', array( $this, 'expire_user_now' ) );

		// Profile Fields
		add_action( 'show_user_profile', array( $this, 'extra_user_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'extra_user_profile_fields' ) );
		add_action( 'user_new_form', array( $this, 'user_new_form' ) );

		// Save Fields
		add_action( 'personal_options_update', array( $this, 'save_extra_user_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_extra_user_profile_fields' ) );
		add_action( 'user_register', array( $this, 'save_extra_user_profile_fields' ) );

		// Scripts and Styles
		add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// User Column
		add_filter( 'manage_users_columns', array( $this, 'manage_users_columns' ) );
		add_action( 'manage_users_custom_column', array( $this, 'manage_users_custom_column' ), 10, 3 );

		// User Column - Sortable
		add_filter( 'manage_users_sortable_columns', array( $this, 'manage_users_columns_sortable' ) );
		add_action( 'pre_get_users', array( $this, 'manage_users_custom_column_sort' ) );

		add_filter( 'user_row_actions', array( $this, 'user_row_actions' ), 10, 2 );

	}

	/**
	 * Expire User Now
	 */
	public function expire_user_now() {

		// Verify nonce
		if ( isset( $_GET['expire_users_nonce'] ) && wp_verify_nonce( $_GET['expire_users_nonce'], 'expire-user-now' ) ) {

			if ( isset( $_GET['expire-user'] ) ) {

				// Expire user now
				$user = new Expire_User( absint( $_GET['expire-user'] ) );
				$user->set_expire_timestamp( current_time( 'timestamp' ) );
				$user->save_user();
				$user->expire();

			}

			wp_safe_redirect( remove_query_arg( array( 'expire-user', 'expire_users_nonce' ) ) );

		}

	}

	/**
	 * User Admin Row Action Links
	 *
	 * @param   array    $actions      Action links.
	 * @param   WP_User  $user_object  User object.
	 * @return  array                  Action links.
	 */
	function user_row_actions( $actions, $user_object ) {

		if ( $this->current_expire_user_can( 'expire_users_edit' ) && $user_object->ID != get_current_user_id() ) {

			$u = new Expire_User( $user_object->ID );

			if ( ! $u->is_expired() ) {

				$url = add_query_arg( 'expire-user', $user_object->ID );
				$url = wp_nonce_url( $url, 'expire-user-now', 'expire_users_nonce' );

				$actions['expire'] = sprintf( '<a class="submitexpire" href="%s">%s</a>', esc_url( $url ), esc_html__( 'Expire Now', 'expire-users' ) );

			}

		}

		return $actions;

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
			$u = new Expire_User( $user_id );
			$expire_date = get_user_meta( $user_id, '_expire_user_date', true );
			if ( $expire_date ) {

				// Check if user should have expired - if so, do it now rather than waiting for schedule
				if ( ! $u->is_expired() ) {
					$u->maybe_expire();
				}

				$value = date_i18n( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), $expire_date );
				if ( $u->is_expired() ) {
					$value = date_i18n( get_option( 'date_format' ), $expire_date );
					$value = '<span class="expire-user-expired"><strong>' . $value . '</strong> <em>' . __( '(expired)', 'expire-users' ) . '</em></span>';
				}
			}
		}
		return $value;
	}

	/**
	 * Make Custom Users Admin Column Sortable
	 */
	function manage_users_columns_sortable( $columns ) {
		$columns['expire_user'] = 'expire_user_date';
		return $columns;
	}

	/**
	 * Sort User Query by Expiry Date
	 */
	function manage_users_custom_column_sort( $query ) {
		if ( 'expire_user_date' == $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', '_expire_user_date' );
		}
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
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		$user = new Expire_User( $user_id );
		$user->set_expire_data( $_POST );
		$user->save_user();
	}

	/**
	 * Profile Fields
	 *
	 * @param  object|null  $user  Instance of WP_User.
	 */
	public function profile_fields( $user = null ) {

		$can_edit_profile_expiry = $this->current_expire_user_can( 'expire_users_edit' );

		if ( $user ) {
			$expire_user = new Expire_User( $user->ID );
		} else {
			$expire_user = new Expire_User();
		}
		
		// Default Expire Date Field Values
		$radio_never      = '';
		$radio_date       = '';
		$days_n           = 7;
		$date_in_block    = 'days';
		$expire_timestamp = current_time( 'timestamp' ) + WEEK_IN_SECONDS;
		$month_n          = '';

		if ( isset( $expire_user->expire_timestamp ) && is_numeric( $expire_user->expire_timestamp ) ) {
			$radio_date = checked( true, true, false );
			$days_n2 = floor( ( $expire_user->expire_timestamp - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
			if ( $days_n2 > 0 ) {
				$days_n = $days_n2;
			}
			$expire_timestamp = $expire_user->expire_timestamp;
			$days_n = ceil( ( $expire_timestamp - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
			if ( $days_n % 7 == 0 ) {
				$days_n = $days_n / 7;
				$date_in_block = 'weeks';
			}
		} else {
			$radio_never = checked( true, true, false );
		}

		$month_n = date( 'm', $expire_timestamp );

		?>

		<h3><?php esc_html_e( 'User Expiry Information', 'expire-users' ); ?></h3>

		<table class="form-table">
			<tr>
				<th><label for="address"><?php esc_html_e( 'Expire Date', 'expire-users' ); ?></label></th>
				<td>

					<?php if ( $expire_user->user_id > 0 ) { ?>
						<div class="misc-pub-section curtime misc-pub-section-last" style="padding-left:0px;">
							<span id="timestamp"><?php echo $expire_user->get_expire_date_display(); ?></span>
							<?php if ( $can_edit_profile_expiry ) { ?>
								<a href="#delete_user_edit_timestamp" class="delete-user-edit-timestamp hide-if-no-js" tabindex='4'><?php esc_html_e( 'Edit', 'expire-users' ) ?></a>
							<?php } ?>
						</div>
					<?php } ?>

					<?php if ( $can_edit_profile_expiry ) { ?>
						<fieldset class="expire-user-date-options <?php if ( $expire_user->user_id > 0 ) echo 'hide-if-js'; ?>">
							<legend class="screen-reader-text"><span><?php esc_html_e( 'Expiry Date', 'expire-users' ); ?></span></legend>
							<label for="expire_user_date_type_never">
								<input name="expire_user_date_type" type="radio" id="expire_user_date_type_never" value="never" <?php echo $radio_never; ?>>
								<?php esc_html_e( 'Never', 'expire-users' ); ?>
							</label><br />
							<label for="expire_user_date_type_in">
								<input name="expire_user_date_type" type="radio" id="expire_user_date_type_in" value="in">
								<?php esc_html_e( 'In', 'expire-users' ); ?> <input type="text" id="expire_user_date_in_num" name="expire_user_date_in_num" value="<?php echo $days_n; ?>" size="3" maxlength="3" tabindex="4" autocomplete="off">
								<select name="expire_user_date_in_block" id="expire_user_date_in_block">
									<?php echo $this->date_block_menu_options( $date_in_block ); ?>
								</select>
							</label><br />
							<label for="expire_user_date_type_date">
								<input name="expire_user_date_type" type="radio" id="expire_user_date_type_date" value="on" <?php echo $radio_date; ?>>
								<?php esc_html_e( 'On', 'expire-users' ); ?> <select id="expire_user_date_on_mm" name="expire_user_date_on_mm" tabindex="4">
									<?php echo $this->month_menu_options( $month_n ); ?>
								</select>
								<input type="text" id="expire_user_date_on_dd" name="expire_user_date_on_dd" value="<?php echo esc_attr( date( 'd', $expire_timestamp ) ); ?>" size="2" maxlength="2" tabindex="4" autocomplete="off">, 
								<input type="text" id="expire_user_date_on_yyyy" name="expire_user_date_on_yyyy" value="<?php echo esc_attr( date( 'Y', $expire_timestamp ) ); ?>" size="4" maxlength="4" tabindex="4" autocomplete="off">
								@ <input type="text" id="expire_user_date_on_hrs" name="expire_user_date_on_hrs" value="<?php echo esc_attr( date( 'H', $expire_timestamp ) ); ?>" size="2" maxlength="2" tabindex="4" autocomplete="off">
								: <input type="text" id="expire_user_date_on_min" name="expire_user_date_on_min" value="<?php echo esc_attr( date( 'i', $expire_timestamp ) ); ?>" size="2" maxlength="2" tabindex="4" autocomplete="off">
							</label>
						</fieldset>
					<?php } ?>

				</td>
			</tr>
			<?php if ( $can_edit_profile_expiry ) { ?>
				<tr>
					<th><label for="role"><?php esc_attr_e( 'On Expire, Default to Role', 'expire-users' ); ?></label></th>
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
					<th><label><?php esc_attr_e( 'Expire Actions', 'expire-users' ); ?></label></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php esc_attr_e( 'Expire Actions', 'expire-users' ); ?></span></legend>
							<label for="expire_user_reset_password">
								<input name="expire_user_reset_password" type="checkbox" id="expire_user_reset_password" value="Y" <?php checked( $expire_user->on_expire_user_reset_password ); ?>>
								<?php esc_attr_e( 'Replace user\'s password with a randomly generated one', 'expire-users' ); ?></a>
							</label><br>
							<label for="expire_user_remove_expiry">
								<input name="expire_user_remove_expiry" type="checkbox" id="expire_user_remove_expiry" value="Y" <?php checked( $expire_user->on_expire_user_remove_expiry ); ?>>
								<?php esc_attr_e( 'Remove expiry details and allow user to continue to login', 'expire-users' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th><label><?php esc_attr_e( 'Email Notifications', 'expire-users' ); ?></label></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php esc_attr_e( 'Email Notifications', 'expire-users' ); ?></span></legend>
							<?php
							$notifications = Expire_User_Notifications_Admin::get_notifications();
							foreach ( $notifications as $notification ) {
								$checked = '';
								$name = $notification['name'];
								if ( 'expire_users_notification_message' == $name ) {
									$name = 'expire_user_email';
									$checked = checked( 1, $expire_user->on_expire_user_email, false );
								} elseif ( 'expire_users_notification_admin_message' == $name ) {
									$name = 'expire_user_email_admin';
									$checked = checked( 1, $expire_user->on_expire_user_email_admin, false );
								}
								?>
								<label for="<?php echo esc_attr( $name ); ?>" title="<?php echo esc_attr( $notification['description'] ); ?>">
									<input name="<?php echo esc_attr( $name ); ?>" type="checkbox" id="<?php echo esc_attr( $name ); ?>" value="Y"<?php echo $checked; ?> />
									<?php echo esc_html( $notification['notification'] ); ?>
								</label><br />
								<?php
							}
							?>
							<br /><a href="<?php echo admin_url( 'users.php?page=expire_users' ); ?>"><?php esc_attr_e( 'View and configure messages', 'expire-users' ); ?></a>
						</fieldset>
					</td>
				</tr>
			<?php } ?>
		</table>

		<?php
	}

	/**
	 * Extra User Profile Fields
	 *
	 * Adds fields to the edit user admin screen.
	 *
	 * @param  object  $user  Instance of WP_User.
	 */
	public function extra_user_profile_fields( $user ) {

		$this->profile_fields( $user );

	}

	/**
	 * New User Form
	 *
	 * Adds fields to the new user admin screen.
	 *
	 * @param  string  $context  Content of the new user form ('add-new-user' or 'add-existing-user').
	 */
	public function user_new_form( $context ) {

		if ( 'add-new-user' == $context && $this->current_expire_user_can( 'expire_users_edit' ) ) {
			$this->profile_fields();
		}

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
			$output .= '<option value="' . esc_attr( $value ) . '" ' . selected( $selected, $value, false ) . '>' . esc_html( $label ) . '</option>';
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
			$output .= '<option value="' . esc_attr( $value ) . '" ' . selected( $selected, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}
		return $output;
	}

	/**
	 * Admin Print Styles
	 */
	function admin_print_styles() {
		if ( $this->is_admin_screen( array( 'users_page_expire_users', 'user-edit', 'profile', 'users' ) ) ) {
			if ( file_exists( WP_PLUGIN_DIR . '/expire-users/css/admin.css' ) ) {
				wp_register_style( 'css-layouts-admin', plugins_url( 'css/admin.css', dirname( __FILE__ ) ) );
				wp_enqueue_style( 'css-layouts-admin' );
			}
		}
	}

	/**
	 * Admin Enqueue Scripts
	 */
	function admin_enqueue_scripts() {
		wp_register_script( 'expire-users-admin-user', plugins_url( 'js/admin-user.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0' );
		wp_localize_script( 'expire-users-admin-user', 'expire_users_admin_user_i18n', array(
			'cancel' => __( 'Cancel', 'expire-users' ),
			'edit'   => __( 'Edit', 'expire-users' )
		) );

		// Only load admin user JavaScript on the pages it may be needed
		if ( $this->is_admin_screen( array( 'users_page_expire_users', 'user-edit', 'profile', 'users' ) ) ) {
			wp_enqueue_script( 'expire-users-admin-user' );
		}
	}

	/**
	 * Check if a specific admin screen is being displayed.
	 *
	 * @param   string|array  $screen_id  Screen ID or array of IDs.
	 * @return  boolean
	 */
	function is_admin_screen( $screen_id ) {
		if ( is_admin() && function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( is_array( $screen_id ) ) {
				foreach ( $screen_id as $id ) {
					if ( $id == $screen->id ) {
						return true;
					}
				}
			} elseif ( $screen_id == $screen->id ) {
				return true;
			}
		}
		return false;
	}

}
