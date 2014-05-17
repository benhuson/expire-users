<?php

class Expire_Users_Notifications {

	var $post_type = 'expire_users_notific';

	/**
	 * Setup notification post type, hooks and filters.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	/**
	 * Registers a notification post type for managing
	 * notification email content and settings.
	 */
	public function register_post_types() {
		$args = array(
			'public'       => true,
			'hierarchical' => false
		);
		register_post_type( $this->post_type, $args );
	}

	/**
	 * Get Notifications
	 *
	 * @todo   Temporary notifications data. Needs converting to post type.
	 */
	public function get_notifications() {
		$notifications = get_posts( array(
			'post_type' => $this->post_type
		) );

		// Temporary notifications data.
		// Needs converting to post type.
		$notifications = array(
			new Expire_Users_Notification( array(
				'name'         => 'expire_users_notification_message',
				'notification' => __( 'User Expired Notification', 'expire-users' ),
				'description'  => __( 'This email is sent to a user when their login details expire.', 'expire-users' ),
				'message'      => get_option( 'expire_users_notification_message' )
			) ),
			new Expire_Users_Notification( array(
				'name'         => 'expire_users_notification_admin_message', 
				'notification' => __( 'User Expired Admin Notification', 'expire-users' ),
				'description'  => __( 'This email is sent to the WordPress admin email address when a user expires.', 'expire-users' ),
				'message'      => get_option( 'expire_users_notification_admin_message' )
			) )
		);

		return apply_filters( 'expire_users_notifications', $notifications );
	}

	public function add_notification( $notification ) {
	}

}

/**
 * Notification Class
 */
class Expire_Users_Notification {

	private $post = null;

	/**
	 * Retrieve notification post if ID or object supplied.
	 *
	 * @param  integer|object|array  $post_id  Post ID, post object or array of data.
	 */
	public function __construct( $post_id = 0 ) {
		if ( is_array( $post_id ) ) {
			$this->set_data( $post_id );
		} else {
			$this->post = get_post( $post_id, ARRAY_A );
		}
	}

	/**
	 * Get an array of notification fields mapped to post fields.
	 *
	 * @internal
	 *
	 * @return  array  Array of fields.
	 */
	private function get_fields_map() {
		return array(
			'name'         => 'post_name',
			'notification' => 'post_title',
			'description'  => 'post_excerpt',
			'message'      => 'post_content'
		);
	}

	/**
	 * Get post field key
	 * Returns the post key based on a notification key.
	 *
	 * @internal
	 *
	 * @param   string  $key  Mapped key to retrieve.
	 * @return  string        Field.
	 */
	private function get_post_key( $key ) {
		$key = $this->sanitize_key( $key );
		$notification_fields = $this->get_fields_map();
		if ( array_key_exists( $key, $notification_fields ) ) {
			$key = $notification_fields[ $key ];
		}
		return $key;
	}

	/**
	 * Get notification field key
	 * Returns the notification key based on a post key.
	 *
	 * @internal
	 *
	 * @param   string  $key  Mapped key to retrieve.
	 * @return  string        Field.
	 */
	private function get_notification_key( $key ) {
		$key = $this->sanitize_key( $key );
		$notification_fields = $this->get_fields_map();
		$mapped_key = array_search( $key, $notification_fields );
		if ( false !== $mapped_key ) {
			$key = $mapped_key;
		}
		return $key;
	}

	/**
	 * Get data value.
	 *
	 * @param   string        $key  Data key to retrieve.
	 * @return  string|array        Key value or array of all data.
	 */
	public function get_data( $key = null ) {
		if ( $key && is_array( $this->post ) ) {
			$key = $this->get_post_key( $key );
			if ( array_key_exists( $key, $this->post ) ) {
				return $this->post[ $key ];
			}
		}
		return $this->post;
	}

	/**
	 * Set data.
	 *
	 * @param  string|array  $data   Data key or array of keys and values.
	 * @param  string        $value  Optional. Value if $data is not an array.
	 */
	public function set_data( $data, $value = '' ) {
		if ( ! is_array( $data ) ) {
			$data = array( $this->sanitize_key( $data ) => $value );
		}
		foreach ( $data as $key => $value ) {
			$key = $this->get_post_key( $key );
			$this->post[ $key ] = $value;
		}
	}

	/**
	 * Sanitize Key.
	 * Don't sanitize ID as it will be converted to lowercase.
	 *
	 * @internal
	 *
	 * @param   string  $key  Key to sanitize.
	 * @return  string
	 */
	private function sanitize_key( $key ) {
		if ( 'ID' == $key ) {
			return $key;
		}
		return sanitize_key( $key );
	}

}
