<?php
/**
 * Handles bulk actions for Spam Super Blocker.
 */

// Add "Mark Spam & Block" to bulk actions.
function ssb_add_bulk_action($bulk_actions) {
  $bulk_actions['mark_spam_block'] = __('Mark Spam & Block', 'spam-super-blocker');
  return $bulk_actions;
}
add_filter('bulk_actions-edit-comments', 'ssb_add_bulk_action');

// Handle "Mark Spam & Block" bulk action.
function ssb_handle_bulk_action($redirect_to, $doaction, $comment_ids) {
  if ($doaction === 'mark_spam_block') {
    foreach ($comment_ids as $comment_id) {
      $comment = get_comment($comment_id);
      if ($comment) {
        // Mark comment as spam.
        wp_spam_comment($comment_id);

        // Add user data to blocked keywords.
        $disallowed_keys = get_option('disallowed_keys', '');
        $allowed_words = get_option('ssb_allowed_words', '');
        $allowed_words_array = array_filter(array_map('trim', explode("\n", $allowed_words)));
        $new_keys = [];

        $block_name = get_option('ssb_block_name', false);
        $block_email = get_option('ssb_block_email', true);
        $block_url = get_option('ssb_block_url', true);

        if ($block_name) {
          $new_keys[] = $comment->comment_author;
        }
        if ($block_email) {
          $new_keys[] = $comment->comment_author_email;
        }
        if ($block_url) {
          $new_keys[] = $comment->comment_author_url;
        }

        $new_keys = array_diff($new_keys, $allowed_words_array);
        $updated_keys = implode("\n", array_unique(array_filter(array_merge(explode("\n", $disallowed_keys), $new_keys))));
        update_option('disallowed_keys', $updated_keys);
      }
    }
    $redirect_to = add_query_arg('bulk_mark_spam_blocked', count($comment_ids), $redirect_to);
  }
  return $redirect_to;
}
add_filter('handle_bulk_actions-edit-comments', 'ssb_handle_bulk_action', 10, 3);
