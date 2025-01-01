=== Spam Super Blocker ===
Contributors: @askasjeremy  
Tags: spam, comments, moderation, keywords  
Requires at least: 5.0  
Tested up to: 6.3  
Requires PHP: 7.2  
Stable tag: 1.0.0 
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  
Donate link: https://mediafri.com/wp/plugins/spam_super_blocker/donate.php

A simple WordPress plugin to help website admins block and clear spam comments collaboratively with ease.

== Description ==

Spam Super Blocker empowers WordPress admins to efficiently manage spam comments with features like:
- Automatically blocking spam keywords.
- Adding "Mark Spam" as a bulk action in the comments page.
- Auto-emptying spam and trash folders based on customizable thresholds.
- Fetching known spam keywords from an online repository.

== Features ==
1. Add spam email and URL values to the "Disallowed Comment Keys" list.
2. Automatically block spam keywords.
3. Auto-clear trash and spam folders with configurable limits.
4. Fetch additional spam keywords from a public API.

== Installation ==

1. Upload the `spam-super-blocker` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to `Settings > Spam Super Blocker` to configure the plugin.

== Changelog ==

= 1.0 =
* Initial release of the plugin.
* Added features for bulk spam marking, auto keyword blocking, and customizable settings.

== Frequently Asked Questions ==

= What happens to disallowed keywords when I deactivate the plugin? =
The disallowed keywords list remains intact after deactivation.

= Can I manually edit the disallowed keywords list? =
Yes, you can edit the "Disallowed Comment Keys" list from the WordPress Discussion settings.

== Screenshots ==

1. **Settings Page**: Configure auto-blocking, emptying trash/spam, and API keyword updates.
2. **Bulk Actions**: Mark multiple comments as spam directly from the Comments page.

== Upgrade Notice ==

= 1.0 =
Ensure you have tested the plugin on a staging site before deploying it to production.
