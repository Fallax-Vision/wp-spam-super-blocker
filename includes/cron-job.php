<?php
/**
 * Cron jobs for Spam Super Blocker.
 */

// // Schedule cron events on plugin activation.
// function ssb_schedule_cron_jobs() {
//   if (!wp_next_scheduled('ssb_empty_trash_event')) {
//     wp_schedule_event(time(), 'daily', 'ssb_empty_trash_event');
//   }

//   if (!wp_next_scheduled('ssb_empty_spam_event')) {
//     wp_schedule_event(time(), 'daily', 'ssb_empty_spam_event');
//   }
// }
// add_action('ssb_empty_trash_event', 'ssb_handle_trash_cleaning');
// add_action('ssb_empty_spam_event', 'ssb_handle_spam_cleaning');


// Run cron job every minute
function ssb_cron_every_minute($schedules) {
  $schedules['every_minute'] = array(
    'interval' => 60, // 60 seconds
    'display'  => __('Every Minute')
  );
  return $schedules;
}
add_filter('cron_schedules', 'ssb_cron_every_minute');


// Run cron job every minute
function ssb_cron_every_five_minute($schedules) {
  $schedules['every_five_minute'] = array(
    'interval' => 300, // 5 minutes
    'display'  => __('Every 5 Minute')
  );
  return $schedules;
}
add_filter('cron_schedules', 'ssb_cron_every_five_minute');

// Schedule cron events on plugin activation.
function ssb_schedule_cron_jobs() {
  if (!wp_next_scheduled('ssb_empty_trash_event')) {
    wp_schedule_event(time(), 'every_five_minute', 'ssb_empty_trash_event');
  }

  if (!wp_next_scheduled('ssb_empty_spam_event')) {
    wp_schedule_event(time(), 'every_five_minute', 'ssb_empty_spam_event');
  }
}
add_action('ssb_empty_trash_event', 'ssb_handle_trash_cleaning');
add_action('ssb_empty_spam_event', 'ssb_handle_spam_cleaning');





// Clear cron events on plugin deactivation.
function ssb_clear_cron_jobs() {
  wp_clear_scheduled_hook('ssb_empty_trash_event');
  wp_clear_scheduled_hook('ssb_empty_spam_event');
}

// Handle trash cleaning based on threshold.
function ssb_handle_trash_cleaning() {
  $auto_empty_trash = get_option('ssb_auto_empty_trash', false);
  $trash_threshold = intval(get_option('ssb_trash_limit', 0));

  error_log('Trash cleaning executed at: ' . current_time('mysql'));

  if ($auto_empty_trash && $trash_threshold > 0) {
    global $wpdb;

    // Count comments in the trash.
    $trash_count = $wpdb->get_var("
      SELECT COUNT(*)
      FROM {$wpdb->comments}
      WHERE comment_approved = 'trash'
    ");

    // Empty trash if threshold is reached.
    if ($trash_count >= $trash_threshold) {
      $wpdb->query("
        DELETE FROM {$wpdb->comments}
        WHERE comment_approved = 'trash'
      ");
    }
  }
}

// Handle spam cleaning based on threshold.
function ssb_handle_spam_cleaning() {
  $auto_empty_spam = get_option('ssb_auto_empty_spam', false);
  $spam_threshold = intval(get_option('ssb_spam_limit', 0));

  error_log('Spam cleaning executed at: ' . current_time('mysql'));

  if ($auto_empty_spam && $spam_threshold > 0) {
    global $wpdb;

    // Count comments in the spam.
    $spam_count = $wpdb->get_var("
      SELECT COUNT(*)
      FROM {$wpdb->comments}
      WHERE comment_approved = 'spam'
    ");

    // Empty spam if threshold is reached.
    if ($spam_count >= $spam_threshold) {
      $wpdb->query("
        DELETE FROM {$wpdb->comments}
        WHERE comment_approved = 'spam'
      ");
    }
  }
}
