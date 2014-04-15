<?php

add_filter( 'plugin_row_meta', array( 'Expire_User_Admin_Plugin', 'plugin_row_meta' ), 10, 4 );
add_filter( 'plugin_action_links_expire-users/expire-users.php', array( 'Expire_User_Admin_Plugin', 'plugin_action_links' ) );

class Expire_User_Admin_Plugin {

	/**
	 * Plugin Row Meta
	 *
	 * Adds GitHub links below the plugin description on the plugins page.
	 *
	 * @param   array   $plugin_meta  Plugin meta display array.
	 * @param   string  $plugin_file  Plugin reference.
	 * @param   array   $plugin_data  Plugin data.
	 * @param   string  $status       Plugin status.
	 * @return  array                 Plugin meta array.
	 */
	static function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		if ( 'expire-users/expire-users.php' == $plugin_file ) {
			$plugin_meta[] = sprintf( '<a href="%s">%s</a>', __( 'https://github.com/benhuson/expire-users', 'password-protected' ), __( 'GitHub', 'expire-users' ) );
			$plugin_meta[] = sprintf( '<a href="%s">%s</a>', __( 'https://github.com/benhuson/expire-users/wiki', 'password-protected' ), __( 'Documentation', 'expire-users' ) );
		}
		return $plugin_meta;
	}

	/**
	 * Plugin Action Links
	 *
	 * Adds settings link on the plugins page.
	 *
	 * @param   array  $actions  Plugin action links array.
	 * @return  array            Plugin action links array.
	 */
	static function plugin_action_links( $actions ) {
		$actions[] = sprintf( '<a href="%s">%s</a>', admin_url( 'users.php?page=expire_users' ), __( 'Settings', 'expire-users' ) );
		return $actions;
	}

}
