<?php

class Expire_Users_Cron {

	function Expire_Users_Cron() {
		add_action( 'expire_user_cron', array( $this, 'do_cron' ) );
		add_action( 'wp', array( $this, 'schedule_cron' ) );

		// @todo Remove - this is just for testing
		//add_action( 'init', array( $this, 'do_cron' ) );
	}

	/**
	 * Schedule cron
	 */
	function schedule_cron() {
		if ( ! wp_next_scheduled( 'expire_user_cron' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'expire_user_cron' ); // hourly, daily or twicedaily
		}
	}

	/**
	 * Do the scheduler
	 *
	 * @todo Is there a more efficient way to query non-expired users
	 * @todo Kick current user if expired
	 */
	function do_cron() {
		//$current_user = wp_get_current_user();
		$maybe_expire_users = new WP_User_Query( array(
			'meta_key'     => '_expire_user_expired',
			'meta_value'   => 'N',
			'meta_compare' => '='
		) );
		if ( $maybe_expire_users->results > 0 ) {
			foreach ( $maybe_expire_users->results as $expired_user ) {
				//if ( $expired_user->ID != $current_user->ID ) {
					$expire_date = get_user_meta( $expired_user->ID, '_expire_user_date', true );
					if ( $expire_date < current_time( 'timestamp' ) ) {
						$this_expire_user = new Expire_User( $expired_user->ID );
						$this_expire_user->expire();
					}
				//}
			}
		}
	}

}
