jQuery(document).ready( function($) {
	$(".delete-user-edit-timestamp").click(function(e){
		$(".expire-user-date-options").toggle();
		if ($(this).text() == 'Cancel') {
			$(this).text('Edit');
		} else {
			$(this).text('Cancel');
		}
	});
} );
