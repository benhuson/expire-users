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
	
	function authenticate( $user, $username, $password ) {
		$user = get_user_by( 'login', $username );
		if ( $user ) {
			$expired = get_user_meta( $user->ID, '_expire_user_expired', true );
			if ( $expired == 'Y' ) {
				$user = new WP_Error( 'expire_users_expired', __('<strong>ERROR</strong>: Your user details have expired.' ) );
				remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
			}
		}
		return $user;
	}
	
	function allow_password_reset( $allow, $user_ID ) {
		if ( absint( $user_ID ) > 0 ) {
			$expired = get_user_meta( $user_ID, '_expire_user_expired', true );
			if ( $expired == 'Y' ) {
				$allow = new WP_Error( 'expire_users_expired_password_reset', __('<strong>ERROR</strong>: Your user details have expired so you are no longer able to reset your password.' ) );
			}
		}
		return $allow;
	}
	
	function shake_error_codes( $shake_codes ){
		 $shake_codes[] = 'expire_users_expired';
		 $shake_codes[] = 'expire_users_expired_password_reset';
		 return $shake_codes;
	}
		
}

?>