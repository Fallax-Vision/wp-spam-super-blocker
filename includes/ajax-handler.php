<?php
/**
 * Handles AJAX requests for the Spam Super Blocker plugin.
 */

// Ensure WordPress has loaded before executing the code
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

function ssb_handle_load_new_blocked_words() {
  // Check nonce for security
  if (!isset($_POST['ssb_nonce']) || !wp_verify_nonce($_POST['ssb_nonce'], 'ssb_nonce_action')) {
    wp_send_json_error(array('message' => 'Nonce verification failed.'));
    return;
  }

  // URL of the remote API endpoint
  $api_url = 'https://mediafri.com/wp/api/blocked_wp_keywords.php';

  // Fetch the total count of keywords from the remote API
  $count_response = wp_remote_get($api_url . '?count=1');
  if (is_wp_error($count_response)) {
    wp_send_json_error(array('message' => 'Failed to fetch the total count of keywords.'));
    return;
  }

  $total_count = intval(wp_remote_retrieve_body($count_response));
  if ($total_count === 0) {
    wp_send_json_error(array('message' => 'No keywords available from the API.'));
    return;
  }

  // Fetch the blocked keywords from the remote API in batches
  $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
  $response = wp_remote_get($api_url . '?offset=' . $offset . '&limit=50');

  if (is_wp_error($response)) {
    wp_send_json_error(array('message' => 'Failed to fetch blocked keywords.'));
    return;
  }

  $blocked_keywords = wp_remote_retrieve_body($response);

  if (empty($blocked_keywords)) {
    wp_send_json_error(array('message' => 'No blocked keywords returned.'));
    return;
  }

  // Decode the JSON response from the remote server
  $blocked_keywords_array = json_decode($blocked_keywords, true);

  if (!is_array($blocked_keywords_array)) {
    wp_send_json_error(array('message' => 'Invalid keyword format received.'));
    return;
  }

  // Get existing disallowed keys from the WordPress database
  $existing_keywords = get_option('disallowed_keys', '');
  $existing_keywords_array = array_unique(array_filter(array_map('trim', explode("\n", $existing_keywords))));

  // Get allowed words from the plugin settings
  $allowed_words = get_option('ssb_allowed_words', '');
  $allowed_words_array = array_unique(array_filter(array_map('trim', explode("\n", $allowed_words))));

  // Filter out any allowed words from the blocked keywords
  $blocked_keywords_array = array_diff($blocked_keywords_array, $allowed_words_array);

  // Remove any keywords that are already in the disallowed list
  $new_keywords = array_diff($blocked_keywords_array, $existing_keywords_array);

  if (!empty($new_keywords)) {
    // Merge the new keywords with the existing ones
    $updated_keys = array_unique(array_merge($existing_keywords_array, $new_keywords));

    // Update the disallowed keys option in the database
    update_option('disallowed_keys', implode("\n", $updated_keys));

    wp_send_json_success(array(
      'message' => 'keywords_added_' . count($new_keywords),
      'offset' => $offset + 50,
      'total_count' => $total_count
    ));
  } else {
    wp_send_json_success(array(
      'message' => 'added_0',
      'offset' => $offset + 50,
      'total_count' => $total_count
    ));
  }
}
// Handle the "Load New Blocked Words" AJAX request
add_action('wp_ajax_ssb_fetch_and_update_keywords_incrementally', 'ssb_handle_load_new_blocked_words');
