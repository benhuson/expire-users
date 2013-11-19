<?php

add_action( 'admin_menu', array( 'Expire_User_Admin_Help', 'admin_menu' ) );
add_action( 'expire_users_help_tabs', array( 'Expire_User_Admin_Help', 'help_tabs' ), 5 );

class Expire_User_Admin_Help {

	/**
	 * Admin Menu
	 */
	function admin_menu() {
		add_action( 'load-users_page_expire_users', array( 'Expire_User_Admin_Help', 'add_help_tabs' ), 20 );
	}

	/**
	 * Add Help Tabs
	 */
	function add_help_tabs() {
		global $wp_version;
		if ( version_compare( $wp_version, '3.3', '<' ) )
			return;
		do_action( 'expire_users_help_tabs', get_current_screen() );
	}

	/**
	 * Help Tabs
	 */
	function help_tabs( $current_screen ) {

		// Expiry Settings
		$current_screen->add_help_tab( array(
			'id'      => 'EXPIRE_USERS_SETTINGS',
			'title'   => __( 'Expiry Settings', 'expire-users' ),
			'content' => __( '<p>Activate and configure default expiry settings for users who register via the registration form.</p>', 'expire-users' )
				. __( '<p><strong>Expiry Date</strong><br />Set a user to nevr expire, expire after a period of time or on a specific date.</p>', 'expire-users' )
				. __( '<p><strong>Default to Role</strong><br />When a user expires, you can assign them a different role.</p>', 'expire-users' )
				. __( '<p><strong>Expire Actions</strong><br />Other actions to trigger when a user expires:</p>', 'expire-users' )
				. '<ul>'
				. __( '<li>replace user\'s password with a randomly generated one.</li>', 'expire-users' )
				. __( '<li>send notification email to user.</li>', 'expire-users' )
				. __( '<li>send notification email to admin.</li>', 'expire-users' )
				. '</ul>'
		) );

		// Expiry Settings
		$current_screen->add_help_tab( array(
			'id'      => 'EXPIRE_USERS_NOTIFICATIONS',
			'title'   => __( 'Notification Emails', 'expire-users' ),
			'content' => __( '<p><strong>User Notification Email</strong><br />This email is sent to a user when their login details expire.</p>', 'expire-users' )
				. __( '<p><strong>Admin Notification Email</strong><br />This email is sent to administrators when a user expires.</p>', 'expire-users' )
		) );

	}

}
