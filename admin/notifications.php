<?php

class Expire_User_Notifications_Admin {

	/**
	 * Admin Table
	 *
	 * Display a table of expiry notifications.
	 */
	public static function admin_table() {
		$notifications_table = new Expire_User_Notifications_Table( array(
			'screen' => 'expire-users-notifications-table'
		) );
		$notifications_table->prepare_items(); 
		$notifications_table->display(); 
	}

	/**
	 * Get Notifications
	 *
	 * @return  array  Notifications data array.
	 */
	public static function get_notifications() {
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

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Expire User Notifications Table
 */
class Expire_User_Notifications_Table extends WP_List_Table {

	/**
	 * Prepare Items
	 */
	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items = Expire_User_Notifications_Admin::get_notifications();
	}

	/**
	 * Get Columns
	 *
	 * @return  array  Notification columns.
	 */
	function get_columns() {
		$columns = array(
			//'active'       => __( 'Active', 'expire-users' ),
			'notification' => __( 'Notification', 'expire-users' ),
			'message'      => __( 'Message', 'expire-users' )
		);
		return apply_filters( 'expire_users_notifications_table_columns', $columns );
	}

	/**
	 * Column Active
	 *
	 * @param   array  $item  Column item.
	 * @return  array         Item.
	 */
	function column_active( $item ) {
		global $expire_users;
		$expire_settings = $expire_users->admin->settings->get_default_expire_settings();

		$checked = '';
		$name = $item['name'];
		if ( $item['name'] == 'expire_users_notification_message' ) {
			$checked = checked( 'Y', $expire_settings['expire_user_email'], false );
			$name = 'expire_user_email';
		} elseif ( $item['name'] == 'expire_users_notification_admin_message' ) {
			$checked = checked( 'Y', $expire_settings['expire_user_email_admin'], false );
			$name = 'expire_user_email_admin';
		}

		return sprintf( '<input type="checkbox" id="%s" name="expire_users_default_expire_settings[%1$s]" value="Y"%s />', esc_attr( $name ), $checked );
	}

	/**
	 * Column Notification
	 *
	 * @param   array  $item  Column item.
	 * @return  array         Item.
	 */
	function column_notification( $item ) {
		$actions = apply_filters( 'expire_users_notifications_table_row_actions', array(), $item );
		return sprintf( '<label for="%s">%s</label> <br /><span class="description">%s</span> %s', esc_attr( $item[ 'name' ] ), $item[ 'notification' ], $item[ 'description' ], $this->row_actions( $actions ) );
	}

	/**
	 * Column Message
	 *
	 * @param   array  $item  Column item.
	 * @return  array         Item.
	 */
	function column_message( $item ) {
		$message = sprintf( '<textarea id="%s" name="%1$s" rows="5" cols="50" class="large-text">%2$s</textarea>', esc_attr( $item[ 'name' ] ), $item[ 'message' ] );
		return apply_filters( 'expire_users_notifications_table_message', $message, $item );
	}

	/**
	 * Column Default
	 *
	 * @param   string  $item         Row item.
	 * @param   string  $column_name  Column name.
	 * @return  string                Output.
	 */
	function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Display Table Nav
	 *
	 * @param  object  $which  Instance of Expire_User_Notifications_Table.
	 */
	function display_tablenav( $which ) {
		$tablenav = apply_filters( 'expire_users_notifications_table_tablenav', '', $which );
		if ( ! empty( $tablenav ) ) {
			printf( '<div class="tablenav %s">%s</div>', $which, $tablenav );
		}
	}

	/**
	 * No Items
	 */
	function no_items() {
		_e( 'No notification defined.', 'expire-users' );
	}

}
