<?php
/**
 * Cleanup on plugin uninstall.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit; // Prevent unauthorized access
}

// Check if the user wants to delete all settings
if (isset($_REQUEST['ssb_delete_settings']) && $_REQUEST['ssb_delete_settings'] === 'yes') {
  // Delete all options related to the plugin
  global $wpdb;
  $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'ssb_%'");
}

// Remove plugin options
delete_option('ssb_auto_block_keywords');
delete_option('ssb_auto_empty_trash');
delete_option('ssb_trash_limit');
delete_option('ssb_auto_empty_spam');
delete_option('ssb_spam_limit');

// Optionally, clear disallowed keys added by the plugin
$disallowed_keys = get_option('disallowed_keys', '');
if (!empty($disallowed_keys)) {
  $plugin_keywords = get_option('ssb_plugin_keywords', []);
  if (!empty($plugin_keywords)) {
    $disallowed_keys_array = array_filter(array_map('trim', explode("\n", $disallowed_keys)));
    $remaining_keys = array_diff($disallowed_keys_array, $plugin_keywords);
    update_option('disallowed_keys', implode("\n", $remaining_keys));
  }
}

// Remove stored plugin-specific data
delete_option('ssb_plugin_keywords');
