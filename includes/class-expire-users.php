<?php

class Expire_Users {
	
	var $admin;
	var $cron;
	var $settings;
	var $user;
	
	function Expire_Users() {
		$this->cron = new Expire_Users_Cron();
		$this->admin = new Expire_User_Admin();
		add_filter( 'authenticate', array( $this, 'authenticate' ), 10, 3 );
		add_filter( 'allow_password_reset', array( $this, 'allow_password_reset' ), 10, 2 );
		add_filter( 'shake_error_codes', array( $this, 'shake_error_codes' ) );
	}
	
	/**
	 * Authenticate
	 */
	function authenticate( $user, $username, $password ) {
		$checkuser = get_user_by( 'login', $username );
		if ( $checkuser ) {
			$expired = get_user_meta( $checkuser->ID, '_expire_user_expired', true );
			if ( $expired == 'Y' ) {
				remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
				return new WP_Error( 'expire_users_expired', __( '<strong>ERROR</strong>: Your user details have expired.', 'expire-users' ) );
			}
		}
		return $user;
	}
	
	/**
	 * Allow Password Reset
	 */
	function allow_password_reset( $allow, $user_ID ) {
		if ( absint( $user_ID ) > 0 ) {
			$expired = get_user_meta( $user_ID, '_expire_user_expired', true );
			if ( $expired == 'Y' ) {
				$allow = new WP_Error( 'expire_users_expired_password_reset', __( '<strong>ERROR</strong>: Your user details have expired so you are no longer able to reset your password.', 'expire-users' ) );
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

?>