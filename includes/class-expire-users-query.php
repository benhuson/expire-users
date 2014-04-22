<?php

class Expire_Users_Query {

	/**
	 * Query
	 *
	 * Pass simple expiry queries to return a User query.
	 * A simple API to save having to construct multiple 'meta_query'
	 * queries each time.
	 *
	 * @param   string|array   $args  Optional. The query variables.
	 * @return  WP_User_Query
	 */
	public static function query( $query = null ) {
		$query = Expire_Users_Query::prepare_query( $query );
		return new WP_User_Query( $query );
	}

	/**
	 * Prepare the query variables.
	 *
	 * @param  string|array  $args  Optional. The query variables.
	 */
	public static function prepare_query( $query = array() ) {
		$query = wp_parse_args( $query, array(
			'expired'              => false,
			'expired_date'         => '',
			'expired_date_compare' => '<'
		) );

		// Expired
		$query['meta_query'][] = array(
			'key'     => '_expire_user_expired',
			'value'   => $query['expired'] ? 'Y' : 'N',
			'compare' => '='
		);

		// Expired Date Compare
		if ( ! empty( $query['expired_date'] ) && is_numeric( $query['expired_date'] ) ) {
			$query['meta_query'][] = array(
				'key'     => '_expire_user_date',
				'value'   => $query['expired_date'],
				'compare' => $query['expired_date_compare'],
				'type'    => 'numeric'
			);
		}

		return $query;
	}

}
