<?php

add_shortcode( 'expire_users_current_user_expire_date', array( 'Expire_Users_Shortcodes', 'current_user_expire_date' ) );

class Expire_Users_Shortcodes {

	/**
	 * [expire_users_current_user_expire_date /]
	 *
	 * Displays the expiry date for the current user.
	 *
	 * Allowed Attributes:
	 * - date_format
	 * - expires_format
	 * - expired_format
	 * - never_expire
	 */
	static function current_user_expire_date( $atts, $content = '' ) {
		if ( is_user_logged_in() ) {
			$u = new Expire_User( get_current_user_id() );
			$atts = shortcode_atts( array(
				'date_format'    => 'M d, Y',
				'expires_format' => 'Expires %s',
				'expired_format' => 'Expired %s',
				'never_expire'   => 'Never Expire',
			), $atts );
			return $u->get_expire_date_display( $atts );
		}
		return $content;
	}

}
