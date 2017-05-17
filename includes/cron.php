<?php

class Expire_Users_Cron {

	public function __construct() {
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
		$maybe_expire_users = Expire_Users_Query::query( array(
			'expired'              => false,
			'expired_date'         => current_time( 'timestamp' ),
			'expired_date_compare' => '<'
		) );
		if ( $maybe_expire_users->results > 0 ) {
			foreach ( $maybe_expire_users->results as $expired_user ) {
				$this_expire_user = new Expire_User( $expired_user->ID );
				$this_expire_user->expire();
			}
		}
	}

}
