
jQuery(document).ready(function($) {

	// Auto Expire Registered Users
	$("input#expire_user_auto_expire_registered_users").change(function(e){
		if (this.checked) {
			$('.expire_user_auto_expire_registered_users_toggle').show();
		} else {
			$('.expire_user_auto_expire_registered_users_toggle').hide();
		}
	});
	if ($("input#expire_user_auto_expire_registered_users:checked").length == 0) {
		$('.expire_user_auto_expire_registered_users_toggle').hide();
	}

	// Edit Timestamp Toggle
	$(".delete-user-edit-timestamp").click(function(e) {
		$(".expire-user-date-options").toggle();
		if ($(this).text() == expire_users_admin_user_i18n.cancel) {
			$(this).text(expire_users_admin_user_i18n.edit);
		} else {
			$(this).text(expire_users_admin_user_i18n.cancel);
		}
	});

});
