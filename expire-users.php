<?php

/*
Plugin Name: Expire Users
Plugin URI: http://www.benhuson.co.uk/
Description: Set expiry dates for user logins.
Version: 0.1
Author: Ben Huson
Author URI: http://www.benhuson.co.uk/
Minimum WordPress Version Required: 3.1
Tested up to: 3.1.2
*/

require_once( dirname( __FILE__ ) . '/includes/class-expire-users.php' );
require_once( dirname( __FILE__ ) . '/includes/class-expire-user.php' );
require_once( dirname( __FILE__ ) . '/includes/class-expire-users-settings.php' );
require_once( dirname( __FILE__ ) . '/includes/class-expire-users-cron.php' );
require_once( dirname( __FILE__ ) . '/admin/class-expire-user-admin.php' );

global $expire_users;
$expire_users = new Expire_Users();

// Clear cron on deactivate
function expire_users_deactivate() {
	wp_clear_scheduled_hook( 'expire_user_cron' );
}
register_deactivation_hook( __FILE__, 'expire_users_deactivate' );

?>