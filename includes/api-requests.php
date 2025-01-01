<?php
/**
 * Handles API requests for Spam Super Blocker.
 */

// Fetch keywords from remote API and update the disallowed keys list.
function ssb_fetch_keywords() {
  check_ajax_referer('ssb_fetch_keywords_nonce', 'security');

  $response = wp_remote_get('https://mediafri.com/wp/api/blocked_wp_keywords.php');
  if (is_wp_error($response)) {
    wp_send_json_error(['message' => 'Failed to fetch keywords from the remote API.']);
  }

  $blocked_keywords = json_decode(wp_remote_retrieve_body($response), true);
  if (empty($blocked_keywords) || !is_array($blocked_keywords)) {
    wp_send_json_error(['message' => 'Invalid data received from the API.']);
  }

  // Get existing disallowed keys and allowed words.
  $existing_keywords = get_option('disallowed_keys', '');
  $existing_keywords_array = array_filter(array_map('trim', explode("\n", $existing_keywords)));
  $allowed_words = get_option('ssb_allowed_words', '');
  $allowed_words_array = array_filter(array_map('trim', explode("\n", $allowed_words)));

  // Prepare new keywords.
  $new_keywords = array_filter(array_map('strtolower', array_map('trim', $blocked_keywords)));
  $existing_keywords_array = array_map('strtolower', $existing_keywords_array);
  $allowed_words_array = array_map('strtolower', $allowed_words_array);

  // Remove allowed words and existing keywords.
  $new_keywords = array_diff($new_keywords, $allowed_words_array, $existing_keywords_array);

  if (!empty($new_keywords)) {
    // Merge new keywords with existing ones.
    $updated_keywords = array_unique(array_merge($existing_keywords_array, $new_keywords));

    // Update the disallowed comment keys option.
    update_option('disallowed_keys', implode("\n", $updated_keywords));

    wp_send_json_success(['new_keywords' => $new_keywords]);
  } else {
    wp_send_json_error(['message' => 'No new keywords to add.']);
  }
}
add_action('wp_ajax_ssb_fetch_keywords', 'ssb_fetch_keywords');
