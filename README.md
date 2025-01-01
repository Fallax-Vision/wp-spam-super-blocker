# Spam Super Blocker
Contributors: @askasjeremy  
Tags: spam, comments, moderation, keywords  
Requires at least: 5.0  
Tested up to: 6.3  
Requires PHP: 7.2  
Stable tag: 1.0.0 
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  
Donate link: https://mediafri.com/wp/plugins/spam_super_blocker/donate.php


## Description
Spam Super Blocker (SSP) is a FREE and Open-Source plugin that empowers WordPress admins to efficiently manage Spam Comments by blocking and clearing spam comments collaboratively, and fetching more known common spam keywords to be automatically blocked in the future.


## Features
1. Allow you to bulk-mark comments as Spam.
2. Automatically add spams' user's name, email, and URL to `Disallowed Comment Keys` under the "Discussion" section to be automatically blocked in the future.
3. Specify `Allowed Keywords` that should never be blocked.
4. Add all current pending comments to "**Spam**" with one click that also blocked the user's name, email, and URL with one click.
5. Empty the "**Spam**" or "**Trash**" folder with one click.
6. Auto-empty "**Spam**" and "**Trash**" folders based on customizable thresholds (number of comments).
7. `Mark Spam & Block` as a bulk action in the comments page to bulk-block spam comments.
>8. Fetch and block known common spam keywords from an online shared repository.

## Installation
1. Upload the `spam-super-blocker` folder to the `/wp-content/plugins/` directory, or the `spam-super-blocker.zip` file to the "**Upload Plugin**" section on the "**Admin dashboard**".
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. To configure the plugin, click on the "Settings" action button below the plugin's name on the Plugins page, or navigate to `Spam Super Blocker` menu item on the Main Menu of WordPress admin dashboard.

## Changelog
= 1.0.0 =
* Initial release of the plugin.
* The GitHub repo of the plugin goes live on: https://github.com/Fallax-Vision/wp-spam-super-blocker
* Added features for bulk spam marking, auto keyword blocking, and customizable settings.
* Added possibility to fetch known common spam keywords from the remote public list and automatically add them to the `Disallowed Comment Keys` in the "**Discussion**" section.

## Frequently Asked Questions
= What happens to disallowed keywords when I deactivate the plugin? =
The disallowed keywords list remains intact after deactivation.

= Can I manually edit the disallowed keywords list? =
Yes, you can edit the "Disallowed Comment Keys" list from the WordPress Discussion settings.

> More Q&A can be found here: https://mediafri.com/wp/plugins/spam_super_blocker/faqs.php

## Screenshots
1. `Actions on Comments Page`: Mark comments as spam and block user's name, email address, and comment URL for future comments.
2. `Actions & Stats`: Add all pending comments to Spam, Empty Spam, Empty Trash, Fetch and Add Known Spam Keywords to "Disallowed Comment Keywords".
- `Bulk Actions`: Mark multiple comments as spam directly from the Comments page.
- `View Stats`: All Comments, My Comments, Pending Comments, Approved Comments, Spam Comments, Trash Comments, Blocked keywords on this website, Allowed keywords on this website, All keywords on remote endpoint, New keywords from remote endpoint.
- `Check for New Spam Keywords` from the remote public list.
3. `Options` : Choose what to add to block list, Auto Block Keywords for Spam, Automatically Empty Trash, Automatically Empty Spam, Allowed Words, etc.
4. `Contributors` : List of current active contributors.

## Upgrade Notice
= 1.0.0 =
Ensure you have tested the plugin on a staging site before deploying it to production.

## How to Contribute?
Simply make a pull request, create your own branch for order, add the features you want, and push to origine. We promise to merge all useful contributions.

## How to Donate?
You can donate via this link: https://buymeacoffee.com/askasjeremy  

The received funds get distributed to all `active contributors`, and help us pay for a few personal needs to allow us continue improving the plugin.