<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * Plugin activation hook.
 */
function spam_super_blocker_activate() {
  // Set default options
  $default_options = [
    'auto_block_keywords' => true,
    'auto_empty_trash' => false,
    'auto_empty_trash_limit' => 50,
    'auto_empty_spam' => false,
    'auto_empty_spam_limit' => 50,
  ];

  foreach ($default_options as $key => $value) {
    if (get_option("spam_super_blocker_{$key}") === false) {
      add_option("spam_super_blocker_{$key}", $value);
    }
  }

  // Ensure disallowed_keys exists
  if (get_option('disallowed_keys') === false) {
    add_option('disallowed_keys', '');
  }

  // Ensure ssb_allowed_words exists
  if (get_option('ssb_allowed_words') === false) {
    add_option('ssb_allowed_words', '');
  }

  // Schedule cron jobs
  ssb_schedule_cron_jobs();
}

/**
 * Plugin deactivation hook.
 */
function spam_super_blocker_deactivate() {
  // Clear cron jobs
  ssb_clear_cron_jobs();
}
