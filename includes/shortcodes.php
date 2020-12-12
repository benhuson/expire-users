<?php

add_shortcode( 'expire_users_current_user_expire_date', array( 'Expire_Users_Shortcodes', 'current_user_expire_date' ) );
add_shortcode( 'expire_users_current_user_expire_countdown', array( 'Expire_Users_Shortcodes', 'current_user_expire_countdown' ) );

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
				'date_format'    => __( 'M d, Y', 'expire-users' ),
				'expires_format' => __( 'Expires %s', 'expire-users' ),
				'expired_format' => __( 'Expired %s', 'expire-users' ),
				'never_expire'   => __( 'Never Expire', 'expire-users' ),
			), $atts );
			return $u->get_expire_date_display( $atts );
		}
		return $content;
	}

	/**
	 * [expire_users_current_user_expire_countdown /]
	 *
	 * Displays the expiry time remaining for the current user.
	 *
	 * Allowed Attributes:
	 * - expires_format
	 * - expired_format
	 * - expired
	 * - never_expire
	 */
	public static function current_user_expire_countdown( $atts, $content = '' ) {

		if ( is_user_logged_in() ) {

			$u = new Expire_User( get_current_user_id() );

			$atts = shortcode_atts( array(
				'expires_format' => __( 'Expires in %s', 'expire-users' ),
				'expired_format' => __( 'Expired %s ago', 'expire-users' ),
				'expired'        => __( 'Expired', 'expire-users' ),
				'never_expire'   => __( 'Never Expire', 'expire-users' )
			), $atts );

			return $u->get_expire_countdown_display( $atts );

		}

		return $content;

	}

}
