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
	 */
	function do_cron() {
		$maybe_expire_users = new WP_User_Query( array(
			'meta_query' => array(
				array(
					'key'     => '_expire_user_expired',
					'value'   => 'N',
					'compare' => '='
				),
				array(
					'key'     => '_expire_user_date',
					'value'   => current_time( 'timestamp' ),
					'compare' => '<',
					'type'    => 'numeric'
				)
			)
		) );
		if ( $maybe_expire_users->results > 0 ) {
			foreach ( $maybe_expire_users->results as $expired_user ) {
				$this_expire_user = new Expire_User( $expired_user->ID );
				$this_expire_user->expire();
			}
		}
	}

}
