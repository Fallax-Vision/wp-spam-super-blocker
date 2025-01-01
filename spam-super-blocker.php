<?php
/**
 * Plugin Name: Spam Super Blocker
 * Plugin URI: https://mediafri.com/wp/plugins/spam_super_blocker
 * Description: A simple plugin that allows you to manage spam comments collaboratively with ease.
 * Version: 1.0.0
 * Text Domain: spam-super-blocker
 * Author: Askas Jeremy
 * Author URI: https://askasjeremy.com
 * GitHub Plugin URI: https://github.com/Fallax-Vision/wp-spam-super-blocker
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Copyright 2025 - Spam Super Blocker - By Askas Jeremy
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

// Include required files.
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/bulk-actions-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/utilities.php';
require_once plugin_dir_path(__FILE__) . 'includes/activation-deactivation.php';
require_once plugin_dir_path(__FILE__) . 'includes/cron-job.php';
require_once plugin_dir_path(__FILE__) . 'includes/api-requests.php';

// Register activation and deactivation hooks.
register_activation_hook(__FILE__, 'spam_super_blocker_activate');
register_deactivation_hook(__FILE__, 'spam_super_blocker_deactivate');

// Add "Mark Spam & Block" to bulk actions.
add_filter('bulk_actions-edit-comments', 'ssb_add_bulk_action');
add_action('handle_bulk_actions-edit-comments', 'ssb_handle_bulk_action', 10, 3);

// Add "Mark Spam & Block" to quick actions.
add_filter('comment_row_actions', 'ssb_add_quick_action', 10, 2);
add_action('admin_post_ssb_mark_spam_block', 'ssb_handle_quick_action');

// Enqueue admin scripts and styles.
add_action('admin_enqueue_scripts', 'ssb_enqueue_admin_assets');
function ssb_enqueue_admin_assets($hook) {
  if ($hook === 'toplevel_page_spam-super-blocker' || $hook === 'edit-comments.php') {
    $script_url = plugins_url('assets/js/admin-scripts.js', __FILE__);
    $style_url = plugins_url('assets/css/admin-styles.css', __FILE__);
    wp_enqueue_script('ssb-admin-scripts', $script_url, ['jquery'], '1.4', true);
    wp_enqueue_style('ssb-admin-styles', $style_url, [], '1.4');
    wp_localize_script('ssb-admin-scripts', 'ssb_ajax', [
      'ajax_url' => admin_url('admin-ajax.php'),
      'ssb_nonce' => wp_create_nonce('ssb_nonce_action')
    ]);
  }
}

// Define the quick action function.
function ssb_add_quick_action($actions, $comment) {
  $actions['mark_spam_block'] = '<a href="' . admin_url('admin-post.php?action=ssb_mark_spam_block&comment_id=' . $comment->comment_ID) . '" class="ssb-mark-spam-block">Mark Spam & Block</a>';
  return $actions;
}

// Handle the quick action form submission.
function ssb_handle_quick_action() {
  if (!isset($_GET['comment_id']) || !current_user_can('edit_posts')) {
    wp_die('Invalid request');
  }

  $comment_id = intval($_GET['comment_id']);
  $comment = get_comment($comment_id);

  if ($comment) {
    // Mark comment as spam
    wp_spam_comment($comment_id);

    // Add name, email, and URL to disallowed comment keys
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

    wp_redirect(admin_url('edit-comments.php'));
    exit;
  } else {
    wp_die('Comment not found');
  }
}

// Register settings.
add_action('admin_init', 'ssb_register_settings');
function ssb_register_settings() {
  register_setting('ssb_options_group', 'ssb_auto_block_keywords');
  register_setting('ssb_options_group', 'ssb_auto_empty_trash');
  register_setting('ssb_options_group', 'ssb_trash_limit');
  register_setting('ssb_options_group', 'ssb_auto_empty_spam');
  register_setting('ssb_options_group', 'ssb_spam_limit');
  register_setting('ssb_options_group', 'ssb_block_name');
  register_setting('ssb_options_group', 'ssb_block_email');
  register_setting('ssb_options_group', 'ssb_block_url');
}

// Handle form submissions for actions.
add_action('admin_post_ssb_handle_actions', 'ssb_handle_actions_form');
function ssb_handle_actions_form() {
  if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
  }

  $action = sanitize_text_field($_POST['action_type']);
  $api_url = 'https://mediafri.com/wp/api/blocked_wp_keywords.php';

  switch ($action) {
    case 'add_pending_to_spam':
      $comments = get_comments(['status' => 'hold']);
      foreach ($comments as $comment) {
        wp_spam_comment($comment->comment_ID);
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
      wp_redirect(admin_url('admin.php?page=spam-super-blocker&tab=actions_stats&ssb_message=pending_to_spam'));
      exit;

    case 'empty_spam':
      $spam_comments = get_comments(['status' => 'spam']);
      foreach ($spam_comments as $comment) {
        wp_delete_comment($comment->comment_ID, true);
      }
      wp_redirect(admin_url('admin.php?page=spam-super-blocker&tab=actions_stats&ssb_message=spam_emptied'));
      exit;

    case 'empty_trash':
      $trash_comments = get_comments(['status' => 'trash']);
      foreach ($trash_comments as $comment) {
        wp_delete_comment($comment->comment_ID, true);
      }
      wp_redirect(admin_url('admin.php?page=spam-super-blocker&tab=actions_stats&ssb_message=trash_emptied'));
      exit;

    case 'refresh_remote':
      setcookie('all_remote_keys', '', time() - 3600, '/');
      setcookie('new_remote_keys', '', time() - 3600, '/');
      wp_redirect(admin_url('admin.php?page=spam-super-blocker&tab=actions_stats&ssb_message=updated_remote_stats'));
      exit;

    default:
      wp_die('Invalid action');
  }
}

// Display admin notices.
add_action('admin_notices', 'ssb_admin_notices');
function ssb_admin_notices() {
  if (isset($_GET['ssb_message'])) {
    $message = '';
    $type = 'success';

    switch ($_GET['ssb_message']) {
      case 'pending_to_spam':
        $message = 'Pending comments moved to spam and users blocked.';
        break;
      case 'spam_emptied':
        $message = 'Spam folder emptied.';
        break;
      case 'trash_emptied':
        $message = 'Trash folder emptied.';
        break;
      case 'keywords_already_added':
        $message = 'All keywords are already in your blocked list.';
        $type = 'info';
        break;
      case 'keywords_API connection failed':
        $message = 'Error: Could not connect to the keywords API. Please try again later.';
        $type = 'error';
        break;
      case 'keywords_No keywords available':
        $message = 'Error: No keywords are available from the API at this time.';
        $type = 'error';
        break;
      case 'keywords_Invalid count response':
        $message = 'Error: Received invalid response from the API. Please try again later.';
        $type = 'error';
        break;
      case 'keywords_Failed fetching keywords':
        $message = 'Error: Failed to fetch keywords from the API. Please try again later.';
        $type = 'error';
        break;
      case 'keywords_Invalid keywords format':
        $message = 'Error: Received invalid keywords format from the API.';
        $type = 'error';
        break;
      case 'keywords_No keywords found':
        $message = 'Error: No keywords were found in the API response.';
        $type = 'error';
        break;
      case 'updated_remote_stats':
        $message = 'Remote stats updated successfully.';
        break;
      case 'error_updating_remote_stats':
        $message = 'Error: Failed to update remote stats. Please try again later.';
        $type = 'error';
        break;
      default:
        if (strpos($_GET['ssb_message'], 'keywords_added_') === 0) {
          $count = intval(str_replace('keywords_added_', '', $_GET['ssb_message']));
          $message = sprintf('%d new keywords have been added to the blocked list.', $count);
          $type = 'success';
        } elseif (strpos($_GET['ssb_message'], 'keywords_error_') === 0) {
          $message = 'Error: The API request failed. Please try again later.';
          $type = 'error';
        }
        break;
    }

    if ($message) {
      $class = 'notice notice-' . $type . ' is-dismissible';
      echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($message) . '</p></div>';
    }
  }
}

// Add settings link to the plugin on the Plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ssb_add_action_links');
function ssb_add_action_links($links) {
  $settings_link = '<a href="' . admin_url('admin.php?page=spam-super-blocker&tab=actions_stats') . '">Settings</a>';
  array_unshift($links, $settings_link);
  return $links;
}

// Add uninstall confirmation dialog
add_action('admin_footer', 'ssb_uninstall_confirmation_dialog');
function ssb_uninstall_confirmation_dialog() {
  ?>
  <div id="ssb-uninstall-confirmation" style="display:none;">
    <p>Do you also want to delete all the stats related to the "Spam Super Blocker" plugin?</p>
    <ul>
      <li>Last modified keywords</li>
      <li>Allowed keywords</li>
      <li>Settings related to auto-block and auto-empty of Spam and Trash folder</li>
    </ul>
    <p>
      <a href="<?php echo wp_nonce_url(admin_url('plugins.php?action=delete-selected&checked[]=spam-super-blocker/spam-super-blocker.php&plugin_status=all&paged=1&ssb_delete_settings=yes'), 'bulk-plugins'); ?>" class="button button-primary">Yes, delete settings too</a>
      <a href="<?php echo wp_nonce_url(admin_url('plugins.php?action=delete-selected&checked[]=spam-super-blocker/spam-super-blocker.php&plugin_status=all&paged=1&ssb_delete_settings=no'), 'bulk-plugins'); ?>" class="button button-secondary">No, only uninstall</a>
    </p>
  </div>
  <script type="text/javascript">
    jQuery(document).ready(function($) {
      $('.delete a').on('click', function(e) {
        e.preventDefault();
        $('#ssb-uninstall-confirmation').dialog({
          resizable: false,
          height: 'auto',
          width: 400,
          modal: true,
          buttons: {
            "Yes, delete settings too": function() {
              window.location.href = $('.button-primary', '#ssb-uninstall-confirmation').attr('href');
            },
            "No, only uninstall": function() {
              window.location.href = $('.button-secondary', '#ssb-uninstall-confirmation').attr('href');
            }
          }
        });
      });
    });
  </script>
  <?php
}
