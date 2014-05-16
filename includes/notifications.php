<?php

class Expire_Users_Notifications {

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Get Notifications
	 *
	 * @todo   Temporary notifications data. Needs converting to post type.
	 */
	public function get_notifications() {
		$notifications = array(
			array(
				'name'         => 'expire_users_notification_message',
				'notification' => __( 'User Expired Notification', 'expire-users' ),
				'description'  => __( 'This email is sent to a user when their login details expire.', 'expire-users' ),
				'message'      => get_option( 'expire_users_notification_message' )
			),
			array(
				'name'         => 'expire_users_notification_admin_message', 
				'notification' => __( 'User Expired Admin Notification', 'expire-users' ),
				'description'  => __( 'This email is sent to the WordPress admin email address when a user expires.', 'expire-users' ),
				'message'      => get_option( 'expire_users_notification_admin_message' )
			)
		);

		return apply_filters( 'expire_users_notifications', $notifications );
	}

}
