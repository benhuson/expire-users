<?php

/*
Plugin Name: Expire Users
Plugin URI: http://wordpress.org/extend/plugins/expire-users/
Description: Set expiry dates for user logins.
Version: 0.8
Author: Ben Huson
Author URI: http://www.benhuson.co.uk/
Minimum WordPress Version Required: 3.5
Tested up to: 3.9
*/

// Version
define( 'EXPIRE_USERS_VERSION', '0.8' );
define( 'EXPIRE_USERS_DB_VERSION', '1' );

// Includes
require_once( dirname( __FILE__ ) . '/includes/expire-users.php' );
require_once( dirname( __FILE__ ) . '/includes/expire-user.php' );
require_once( dirname( __FILE__ ) . '/includes/query.php' );
require_once( dirname( __FILE__ ) . '/includes/settings.php' );
require_once( dirname( __FILE__ ) . '/includes/cron.php' );
require_once( dirname( __FILE__ ) . '/includes/shortcodes.php' );
require_once( dirname( __FILE__ ) . '/admin/plugin.php' );
require_once( dirname( __FILE__ ) . '/admin/settings.php' );
require_once( dirname( __FILE__ ) . '/admin/expire-user.php' );
require_once( dirname( __FILE__ ) . '/admin/notifications.php' );
require_once( dirname( __FILE__ ) . '/admin/help.php' );

// I18n
function expire_users_load_plugin_textdomain() {
	load_plugin_textdomain( 'expire-users', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'expire_users_load_plugin_textdomain' );

global $expire_users;
$expire_users = new Expire_Users();

// Clear cron on deactivate
function expire_users_deactivate() {
	wp_clear_scheduled_hook( 'expire_user_cron' );
}
register_deactivation_hook( __FILE__, 'expire_users_deactivate' );
