
jQuery(document).ready( function($) {
	$(".delete-user-edit-timestamp").click(function(e){
		$(".expire-user-date-options").toggle();
		if ($(this).text() == expire_users_admin_user_i18n.cancel) {
			$(this).text(expire_users_admin_user_i18n.edit);
		} else {
			$(this).text(expire_users_admin_user_i18n.cancel);
		}
	});
} );
