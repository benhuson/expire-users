<?php

class Expire_Users {

	var $admin;
	var $cron;
	var $settings;
	var $user;

	public function __construct() {
		$this->cron = new Expire_Users_Cron();
		$this->admin = new Expire_User_Admin();
		add_filter( 'authenticate', array( $this, 'authenticate' ), 100, 3 );
		add_filter( 'allow_password_reset', array( $this, 'allow_password_reset' ), 10, 2 );
		add_filter( 'shake_error_codes', array( $this, 'shake_error_codes' ) );
		add_action( 'init', array( $this, 'logout_expired_logged_in_user' ) );
		add_action( 'register_form', array( $this, 'register_form' ) );
		add_action( 'user_register', array( $this, 'user_register' ) );
		add_action( 'expire_users_expired', array( $this, 'handle_on_expire_default_to_role' ) );
		add_action( 'expire_users_expired', array( $this, 'handle_on_expire_user_reset_password' ) );
		add_action( 'expire_users_expired', array( $this, 'handle_on_expire_user_email' ) );
		add_action( 'expire_users_expired', array( $this, 'handle_on_expire_user_email_admin' ) );
		add_action( 'expire_users_expired', array( $this, 'handle_on_expire_user_remove_expiry' ) );
		add_filter( 'expire_users_email_notification_message', array( $this, 'email_notification_filter' ), 20, 2 );
		add_filter( 'expire_users_email_admin_notification_message', array( $this, 'email_notification_filter' ), 20, 2 );
		add_filter( 'expire_users_email_notification_subject', array( $this, 'email_notification_filter' ), 20, 2 );
		add_filter( 'expire_users_email_admin_notification_subject', array( $this, 'email_notification_filter' ), 20, 2 );
		add_filter( 'option_expire_users_notification_message', array( $this, 'default_expire_users_notification_message' ) );
		add_filter( 'option_expire_users_notification_admin_message', array( $this, 'default_expire_users_notification_admin_message' ) );
	}

	/**
	 * Logout Expired Logged In User
	 */
	public function logout_expired_logged_in_user() {

		if ( is_user_logged_in() ) {

			// Expire user if past expire date
			$user = new Expire_User( absint( get_current_user_id() ) );
			$expire = $user->maybe_expire();

			// Note expired date and logout
			if ( $expire ) {
				if ( $user->on_expire_user_remove_expiry ) {
					$user->remove_expire_date();
					$user->save_user();
				} else {
					$user->set_expire_timestamp( current_time( 'timestamp' ) );
					$user->save_user();
					wp_clear_auth_cookie();
				}
			}

		}

	}

	/**
	 * Register Form
	 * Adds a hidden field to the register form to flag that a new user should use
	 * the auto-expire settings.
	 */
	function register_form() {
		echo '<input type="hidden" name="expire_users" value="auto" />';
	}

	/**
	 * User Register
	 * Runs on user registration.
	 */
	function user_register( $user_id ) {
		if ( isset( $_POST['expire_users'] ) && 'auto' == $_POST['expire_users'] ) {

			$expire_settings = $this->admin->settings->get_default_expire_settings();

			$expire_data = array(
				'expire_user_date_type'         => $expire_settings['expire_user_date_type'],
				'expire_user_date_in_num'       => $expire_settings['expire_user_date_in_num'],
				'expire_user_date_in_block'     => $expire_settings['expire_user_date_in_block'],
				'expire_user_date_on_timestamp' => $expire_settings['expire_timestamp'],
				'expire_user_role'              => $expire_settings['expire_user_role'],
				'expire_user_reset_password'    => $expire_settings['expire_user_reset_password'],
				'expire_user_email'             => $expire_settings['expire_user_email'],
				'expire_user_email_admin'       => $expire_settings['expire_user_email_admin'],
				'expire_user_remove_expiry'     => $expire_settings['expire_user_remove_expiry']
			);

			$user = new Expire_User( $user_id );
			$user->set_expire_data( $expire_data );
			$user->save_user();
		}
	}

	/**
	 * Change role when user expires?
	 */
	function handle_on_expire_default_to_role( $expired_user ) {
		if ( $expired_user->on_expire_default_to_role ) {
			if ( get_role( $expired_user->on_expire_default_to_role ) ) {
				$u = new WP_User( $expired_user->user_id );
				$u->set_role( $expired_user->on_expire_default_to_role );
			}
		}
	}

	/**
	 * Generate random password when user expires?
	 */
	function handle_on_expire_user_reset_password( $expired_user ) {
		if ( $expired_user->on_expire_user_reset_password ) {
			$password = wp_generate_password( 12, false );
			wp_set_password( $password, $expired_user->user_id );
		}
	}

	/**
	 * Send notification email when user expires?
	 */
	function handle_on_expire_user_email( $expired_user ) {
		if ( $expired_user->on_expire_user_email ) {
			$u = new WP_User( $expired_user->user_id );
			$message = apply_filters( 'expire_users_email_notification_message', get_option( 'expire_users_notification_message' ), $expired_user );
			$subject = apply_filters( 'expire_users_email_notification_subject', __( 'Your login details to %%sitename%% have expired', 'expire-users' ), $expired_user );
			if ( ! empty( $subject ) && ! empty( $message ) ) {
				wp_mail( $u->user_email, $subject, $message );
			}
		}
	}

	/**
	 * Send admin notification email when user expires?
	 */
	function handle_on_expire_user_email_admin( $expired_user ) {
		if ( $expired_user->on_expire_user_email_admin ) {
			$message = apply_filters( 'expire_users_email_admin_notification_message', get_option( 'expire_users_notification_admin_message' ), $expired_user );
			$subject = apply_filters( 'expire_users_email_admin_notification_subject', __( 'Login details to %%sitename%% have expired (%%username%%)', 'expire-users' ), $expired_user );
			if ( ! empty( $subject ) && ! empty( $message ) ) {
				wp_mail( $this->get_admin_email(), $subject, $message );
			}
		}
	}

	/**
	 * Remove expiry details and continue to allow login when user expires?
	 */
	function handle_on_expire_user_remove_expiry( $expired_user ) {
		if ( $expired_user->on_expire_user_remove_expiry ) {
			$expired_user->remove_expire_date();
			$expired_user->save_user();
		}
	}

	/**
	 * Email notification filter
	 */
	function email_notification_filter( $message, $expired_user ) {
		$u = new WP_User( $expired_user->user_id );
		$message = str_replace( '%%name%%', $this->get_user_display_name( $u ), $message );
		$message = str_replace( '%%username%%', $u->user_login, $message );
		$message = str_replace( '%%expirydate%%', date_i18n( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), $expired_user->expire_timestamp ), $message );
		$message = str_replace( '%%sitename%%', get_bloginfo( 'name' ), $message );
		return $message;
	}

	/**
	 * Get Admin Email
	 *
	 * @since  1.0
	 *
	 * @return  string  Email address.
	 */
	function get_admin_email() {
		return apply_filters( 'expire_users_admin_email', get_bloginfo( 'admin_email' ) );
	}

	/**
	 * Get User Display Name
	 *
	 * Tries to retrieve user's real name, otherwise their display name.
	 * Defaults to username if neither exist.
	 *
	 * @since  0.7
	 *
	 * @param   object  $user  WP_User object.
	 * @return  string         Display name.
	 */
	function get_user_display_name( $user ) {
		if ( ! empty( $user->first_name ) ) {
			return trim( $user->first_name . ' ' . $user->last_name );
		} elseif ( ! empty( $user->user_nicename ) ) {
			return $user->user_nicename;
		}
		return $user->user_login;
	}

	function default_expire_users_notification_message( $value ) {
		if ( empty( $value ) ) {
			$value = __( 'Your access to %%sitename%% has expired.', 'expire-users' );
		}
		return $value;
	}

	function default_expire_users_notification_admin_message( $value ) {
		if ( empty( $value ) ) {
			$value = __( 'Access to %%sitename%% has expired for %%name%% (%%username%%) on %%expirydate%%', 'expire-users' );
		}
		return $value;
	}

	/**
	 * Authenticate
	 */
	function authenticate( $user, $username, $password ) {
		$checkuser = get_user_by( 'login', $username );
		if ( $checkuser ) {
			$u = new Expire_User( $checkuser->ID );
			$expired = $u->is_expired();
			if ( ! $expired ) {
				$expired = $u->maybe_expire();
			}
			if ( $expired ) {
				return new WP_Error( 'expire_users_expired', sprintf( '<strong>%s</strong> %s', __( 'ERROR:', 'expire-users' ), __( 'Your user details have expired.', 'expire-users' ) ) );
			}
		}
		return $user;
	}

	/**
	 * Allow Password Reset
	 */
	function allow_password_reset( $allow, $user_ID ) {
		$checkuser = get_user_by( 'ID', $user_ID );
		if ( $checkuser ) {
			$u = new Expire_User( $checkuser->ID );
			if ( $u->is_expired() ) {
				$allow = new WP_Error( 'expire_users_expired_password_reset', sprintf( '<strong>%s</strong> %s', __( 'ERROR:', 'expire-users' ), __( 'Your user details have expired so you are no longer able to reset your password.', 'expire-users' ) ) );
			}
		}
		return $allow;
	}

	/**
	 * Shake Error Codes
	 */
	function shake_error_codes( $shake_codes ){
		 $shake_codes[] = 'expire_users_expired';
		 $shake_codes[] = 'expire_users_expired_password_reset';
		 return $shake_codes;
	}

}
