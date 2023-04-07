=== Expire Users ===
Contributors: husobj
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MW3TJNHM283LU
Tags: users, password, expire, login, roles
Requires at least: 5.4
Tested up to: 6.2
Stable tag: 1.2.1
Requires PHP: 7.4
License: GPLv2 or later

Set expiry dates for user logins.

== Description ==

> Important security update - if you are using version 0.2 or earlier please upgrade

This plugin allows you to set expiry dates for user logins. You can set a user to:

* Never expire (default)
* Expire in X days, weeks, moths or years
* Expire on a specific date

When a user expires you can:

* Change the role of that user
* Replace the user's password with a randomly generated one
* Send an email notification to the user
* Send an email notification to the site administrator
* Remove expiry details and allow user to continue to login
* Perform you own actions using an [`expire_users_expired`](https://github.com/benhuson/expire-users/wiki/expire_users_expired) hook

You can automatically assign expiry details to users who sign up via the register form.

The email notification messages can be configured in the admin settings.

Please post in the [support forum](http://wordpress.org/support/plugin/expire-users) if you have any questions, or refer to the [documentation](https://github.com/benhuson/expire-users/wiki), [report bugs](https://github.com/benhuson/expire-users/issues) and [submit translations](https://github.com/benhuson/expire-users/issues) at the plugin's [GitHub page](https://github.com/benhuson/expire-users/).

== Installation ==

To install and configure this plugin...

1. Upload or install the plugin through your WordPress admin.
2. Activate the plugin via the 'Plugins' admin menu.
3. Edit a user to set password expiry options.

= Upgrading =

If you are upgrading manually via FTP rather that through the WordPress automatic upgrade link, please de-activate and re-activate the plugin to ensure the plugin upgrades correctly.

== Frequently Asked Questions ==

None at present.

== Screenshots ==

1. Expire Date column in users admin.
2. Edit a user's expiry date and expiry actions.
3. Edit email notification messages.
4. User login expired error.

== Changelog ==

= 1.2.1 =
* Tested up to WordPress 6.2
* Tested up to PHP 8.2

= 1.2 =
* Tested up to WordPress 5.6
* Added `expire_users_current_user_expire_countdown` shortcode.
* Fix `expire_users_email_notification_{string}` and `expire_users_email_admin_notification_{string}` being applied in the inverse contexts. Props [@Chaddles23](https://github.com/Chaddles23/)

= 1.1 =
* Make Expire Date column sortable in admin panel. Props @loreboldo.
* Fix error when user tries to reset password.
* Fix issue when expiry check on login happens too early.
* Fix translation of "ERROR:".

= 1.0.4 =
* On expiry, remove expiry date and continue to allow login if set. Props @loreboldo.

= 1.0.3 =
* If user logged in, check expiration while browsing.
* Prepare for translation.

= 1.0.2 =
* Rollback changes causing multiple emails to be sent!

= 1.0.1 =
* Fix PHP7 class constructor warnings.
* Check and expire users if needed when displaying in the admin.
* Check if cron needs setting up when in admin.

= 1.0 =
* Add expiry fields to new user admin page.
* Add 'expire_users_admin_email' filter to allow admin notifications to be sent to a different email address.
* Add French translation. Props ateruel.
* Add Italian translation. Props Marco Chiesi.
* Tested up to WordPress 4.2

= 0.9 =
* Expired dates are displayed as red in admin.
* Dates are correctly internationalized.
* JavaScript and CSS files are only loaded on the required pages in admin.
* Email notifications checkboxes grouped together under new heading.
* Improved expired users database query.
* Added Expire_User->is_expired() method.
* Added version constants.

= 0.8 =
* Added [expire_users_current_user_expire_date] shortcode.
* Force check if user expired on login.
* Fix static method warnings.

= 0.7 =
* Fix expiry dates to work with site's timezone.
* Use date formats from WordPress settings.
* Use user's real name in notification emails if available.
* Ensure custom roles are listed in drop down menu.
* Add option to remove user expiry details and continue to allow user to login when they expire.
* Add admin help.

= 0.6 =
* Added support for translations. Submissions welcome.

= 0.5 =
* Added option to automatically set expiry details for users who register via the register form.

= 0.4 =
* Enables email notifications to users and administrators.
* Add a settings page where you can configure email notification messages.
* Perform you own actions on user expiry using an `expire_users_expired` hook.
* Only allow users with user editing capabilities (administrators) to edit expiration dates.

= 0.3 =

**Important Security Update!**

* Fix authenticate() and login issue.

= 0.2 =
* Prepare for translation.
* Fix 'Expire Date In…' settings.

= 0.1 =
* First Release. If you spot any bugs or issues please [log them here](https://github.com/benhuson/expire-users/issues).

== Upgrade Notice ==

= 1.2.1 =
Tested up to WordPress 6.2 and PHP 8.2

= 1.2 =
Added `expire_users_current_user_expire_countdown` shortcode.

= 1.1 =
Sort users by expiry date in admin and fix some login issues.

= 1.0.4 =
On expiry, remove expiry date and continue to allow login if set.

= 1.0.3 =
If user logged in, check expiration while browsing.

= 1.0.2 =
Rollback changes causing multiple emails to be sent!

= 1.0.1 =
Fix PHP7 class constructor warnings and beter checking for expire users when displaying them in the admin.

= 1.0 =
Added expiry fields to new user admin page and 'expire_users_admin_email' filter.

= 0.9 =
Expired dates are displayed as red. Dates are internationalized and JavaScript/CSS files are only loaded on the required admin pages.

= 0.8 =
Added [expire_users_current_user_expire_date] shortcode and force check if user expired on login.

= 0.7 =
Fix expiry dates to work with site's timezone.

= 0.6 =
Added support for translations.

= 0.5 =
Assign expiry details to users when they register via the register form.

= 0.4 =
Option to enable email notifications and perform custom actions on user expiry.

= 0.3 =
Important security update!

= 0.2 =
Ready for translation. Fixed "Expire Date In..." settings.

= 0.1 =
First release.
