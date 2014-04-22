<?php

add_action( 'admin_head', array( 'Expire_User_Notifications_Admin', 'admin_head' ) );

class Expire_User_Notifications_Admin {

	/**
	 * Admin Head
	 *
	 * Adds settings page styles.
	 */
	public static function admin_head() {
		$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
		if ( 'expire_users' != $page ) {
			return;
		}

		echo '<style type="text/css">';
		echo '.wp-list-table .column-message { width: 75%; }';
		echo '.wp-list-table tfoot { display: none; }';
		echo '</style>';
	}

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
				'notification' => __( 'User Notification Email', 'expire-users' ),
				'message'      => get_option( 'expire_users_notification_message' )
			),
			array(
				'name'         => 'expire_users_notification_admin_message', 
				'notification' => __( 'Admin Notification Email', 'expire-users' ),
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
		return array(
			'notification' => __( 'Notification', 'expire-users' ),
			'message'      => __( 'Message', 'expire-users' )
		);
	}

	/**
	 * Column Default
	 *
	 * @param   string  $item         Row item.
	 * @param   string  $column_name  Column name.
	 * @return  string                Output.
	 */
	function column_default( $item, $column_name ) {
		switch ( $column_name ) { 
			case 'notification':
				return sprintf( '<label for="%s">%s</label>', esc_attr( $item[ 'name' ] ), $item[ $column_name ] );
			case 'message':
				return sprintf( '<textarea id="%s" name="%1$s" rows="5" cols="50" class="large-text">%2$s</textarea>', esc_attr( $item[ 'name' ] ), $item[ $column_name ] );
			default:
				return '';
		}
	}

	/**
	 * Display Table Nav
	 *
	 * @param  object  $which  Instance of Expire_User_Notifications_Table.
	 */
	function display_tablenav( $which ) {
		// Nothing to display
	}

	/**
	 * No Items
	 */
	function no_items() {
		_e( 'No notification defined.', 'expire-users' );
	}

}
