<?php

class Expire_Users_Settings {

	var $on_expire_delete          = false;
	var $on_expire_reset_password  = false;
	var $on_expire_role_subscriber = false;
	var $auto_expire               = 0;
	var $expiring_soon_time        = 604800; // 1 week

	public function __construct() {
	}

	function get_settings() {
	}

	function update_settings() {
	}

	function set_on_expire_reset_password( $bool ) {
	}

	function set_on_expire_email_notification( $bool ) {
	}

	function set_on_expire_delete( $bool ) {
	}

	function set_on_expire_role_subscriber( $bool ) {
	}

	function set_auto_expire( $time ) {
	}

}
