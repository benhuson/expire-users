<?php

add_action( 'admin_menu', array( 'Expire_User_Admin_Help', 'admin_menu' ) );
add_action( 'expire_users_help_tabs', array( 'Expire_User_Admin_Help', 'help_tabs' ), 5 );

class Expire_User_Admin_Help {

	/**
	 * Admin Menu
	 */
	static function admin_menu() {
		add_action( 'load-users_page_expire_users', array( 'Expire_User_Admin_Help', 'add_help_tabs' ), 20 );
		add_action( 'load-user-edit.php', array( 'Expire_User_Admin_Help', 'add_help_tabs' ), 20 );
	}

	/**
	 * Add Help Tabs
	 */
	static function add_help_tabs() {
		global $wp_version;
		if ( version_compare( $wp_version, '3.3', '<' ) ) {
			return;
		}
		do_action( 'expire_users_help_tabs', get_current_screen() );
	}

	/**
	 * Help Tabs
	 */
	static function help_tabs( $current_screen ) {
		if ( 'user-edit' == $current_screen->id ) {

			// Expiry Settings
			$current_screen->add_help_tab( array(
				'id'      => 'EXPIRE_USERS_USER',
				'title'   => __( 'User Expiry Information', 'expire-users' ),
				'content' => __( '<p>Activate and configure default expiry settings for users who register via the registration form.</p>', 'expire-users' )
					. __( '<p><strong>Expiry Date</strong><br />Set a user to never expire, expire after a period of time or on a specific date.</p>', 'expire-users' )
					. __( '<p><strong>Default to Role</strong><br />When a user expires, you can assign them a different role.</p>', 'expire-users' )
					. __( '<p><strong>Expire Actions</strong><br />Other actions to trigger when a user expires:</p>', 'expire-users' )
					. '<ul>'
					. __( '<li>replace user\'s password with a randomly generated one.</li>', 'expire-users' )
					. __( '<li>remove expiry details and allow user to continue to login.</li>', 'expire-users' )
					. '</ul>'
					. sprintf( __( '<p><strong>Notification Emails</strong><br />These emails are sent if you have checked the checkboxes on a user\'s profile. Notification messages are configured on the <a href="%s">Expire Users settings</a> page.</p>', 'expire-users' ), admin_url( 'users.php?page=expire_users' ) )
			) );

			// Add Help Sidebar
			Expire_User_Admin_Help::help_sidebar( $current_screen );

		} elseif ( 'users_page_expire_users' == $current_screen->id ) {

			// Expiry Settings
			$current_screen->add_help_tab( array(
				'id'      => 'EXPIRE_USERS_SETTINGS',
				'title'   => __( 'Expiry Settings', 'expire-users' ),
				'content' => __( '<p>Activate and configure default expiry settings for users who register via the main WordPress registration form.</p>', 'expire-users' )
					. __( '<p><strong>Expiry Date</strong><br />Set a user to never expire, expire after a period of time or on a specific date.</p>', 'expire-users' )
					. __( '<p><strong>Default to Role</strong><br />When a user expires, you can assign them a different role.</p>', 'expire-users' )
					. __( '<p><strong>Expire Actions</strong><br />Other actions to trigger when a user expires:</p>', 'expire-users' )
					. '<ul>'
					. __( '<li>replace user\'s password with a randomly generated one.</li>', 'expire-users' )
					. __( '<li>remove expiry details and allow user to continue to login.</li>', 'expire-users' )
					. '</ul>'
					. __( '<p><strong>Notification Emails</strong><br />These emails are sent if you have checked the checkboxes on a user\'s profile.</p>', 'expire-users' )
					
			) );

			// Add Help Sidebar
			Expire_User_Admin_Help::help_sidebar( $current_screen );

		}
	}

	/**
	 * Help Sidebar
	 */
	static function help_sidebar( $current_screen ) {
		$current_screen->set_help_sidebar(
			'<p><strong>' . __( 'Expire Users Plugin', 'expire-users' ) . '</strong></p>
			<ul>
				<li><a href="https://github.com/benhuson/expire-users/wiki">' . __( 'Documentation', 'expire-users' ) . '</a></li>
				<li><a href="http://wordpress.org/support/plugin/expire-users">' . __( 'Support Forum', 'expire-users' ) . '</a></li>
				<li><a href="https://github.com/benhuson/expire-users">' . __( 'Contribute at Github', 'expire-users' ) . '</a></li>
				<li><a href="https://github.com/benhuson/expire-users/issues">' . __( 'Submit an Issue', 'expire-users' ) . '</a></li>
			</ul>'
		);
	}

}
