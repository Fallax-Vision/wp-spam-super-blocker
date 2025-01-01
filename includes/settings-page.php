<?php

function ssb_render_settings_page() {
  ?>
  <div class="wrap">
    <h1 class="main_title">Spam Super Blocker</h1>
    <h2 class="nav-tab-wrapper" style="margin-bottom: 12px;">
      <a href="?page=spam-super-blocker&tab=actions_stats" class="nav-tab <?php echo ssb_get_active_tab() === 'actions_stats' ? 'nav-tab-active' : ''; ?>">Actions & Stats</a>
      <a href="?page=spam-super-blocker&tab=options" class="nav-tab <?php echo ssb_get_active_tab() === 'options' ? 'nav-tab-active' : ''; ?>">Options</a>
      <a href="?page=spam-super-blocker&tab=roadmap_issues" class="nav-tab <?php echo ssb_get_active_tab() === 'roadmap_issues' ? 'nav-tab-active' : ''; ?>">Roadmap & Issues</a>
      <a href="?page=spam-super-blocker&tab=contributors" class="nav-tab <?php echo ssb_get_active_tab() === 'contributors' ? 'nav-tab-active' : ''; ?>">Contributors</a>
      <a href="?page=spam-super-blocker&tab=about" class="nav-tab <?php echo ssb_get_active_tab() === 'about' ? 'nav-tab-active' : ''; ?>">About</a>
    </h2>

    <?php
    $active_tab = ssb_get_active_tab();
    if ($active_tab === 'options') {
      ssb_render_options_tab();
    } elseif ($active_tab === 'actions_stats') {
      ssb_render_actions_stats_tab();
    } elseif ($active_tab === 'about') {
      ssb_render_about_tab();
    } elseif ($active_tab === 'roadmap_issues') {
      ssb_render_roadmap_and_issues_tab();
    } elseif ($active_tab === 'contributors') {
      ssb_render_contributors_tab();
    }
    ?>
  </div>
  <?php
}

function ssb_render_options_tab() {
  ?>
  <form method="post" action="options.php">
    <?php settings_fields('ssb_options_group'); ?>
    <?php do_settings_sections('ssb_options_group'); ?>

    <style>
      .form-table th {width: 250px;}
    </style>

    <table class="form-table">
      <tr>
        <th scope="row">Choose what to add to block list :</th>
        <td>
          <label><input type="checkbox" name="ssb_block_name" value="1" <?php checked(1, get_option('ssb_block_name', 0)); ?> /> User's Name</label><br>
          <label><input type="checkbox" name="ssb_block_email" value="1" <?php checked(1, get_option('ssb_block_email', 1)); ?> /> User's Email</label><br>
          <label><input type="checkbox" name="ssb_block_url" value="1" <?php checked(1, get_option('ssb_block_url', 1)); ?> /> Comment's URL</label>
        </td>
      </tr>
      <tr>
        <th scope="row">Auto Block Keywords for Spam :</th>
        <td>
          <label><input type="checkbox" name="ssb_auto_block_keywords" value="1" <?php checked(1, get_option('ssb_auto_block_keywords', 0)); ?> /> Automatically add keywords of spam comments to the blocked list.</label>
        </td>
      </tr>
      <tr>
        <th scope="row">Automatically Empty Trash :</th>
        <td>
          <label><input type="checkbox" name="ssb_auto_empty_trash" value="1" <?php checked(1, get_option('ssb_auto_empty_trash', 0)); ?> /> When comments in Trash reach:</label>
          <input type="number" name="ssb_trash_limit" value="<?php echo esc_attr(get_option('ssb_trash_limit', 100)); ?>" placeholder="Number of comments" />
        </td>
      </tr>
      <tr>
        <th scope="row">Automatically Empty Spam :</th>
        <td>
          <label><input type="checkbox" name="ssb_auto_empty_spam" value="1" <?php checked(1, get_option('ssb_auto_empty_spam', 0)); ?> /> When comments in Spam reach:</label>
          <input type="number" name="ssb_spam_limit" value="<?php echo esc_attr(get_option('ssb_spam_limit', 100)); ?>" placeholder="Number of comments" />
        </td>
      </tr>
      <tr>
        <th scope="row">Allowed Words :</th>
        <td>
          <label for="ssb_allowed_words">Words that should not be blocked:</label>
          <br>
          <textarea style="margin-top:10px;" name="ssb_allowed_words" id="ssb_allowed_words" rows="8" cols="60"><?php echo esc_textarea(get_option('ssb_allowed_words', '')); ?></textarea>
        </td>
      </tr>
    </table>

    <?php submit_button(); ?>
  </form>
  <?php
}

function ssb_render_actions_stats_tab() {
  $pending_count = wp_count_comments()->moderated;
  $spam_count = wp_count_comments()->spam;
  $trash_count = wp_count_comments()->trash;
  $all_count = wp_count_comments()->total_comments;
  $approved_count = wp_count_comments()->approved;
  $current_user = wp_get_current_user();
  $my_count = get_comments(array('user_id' => $current_user->ID, 'count' => true));
  $disallowed_keys = get_option('disallowed_keys', '');
  $disallowed_keys_array = array_filter(array_map('trim', explode("\n", $disallowed_keys)));
  $disallowed_keys_count = count(array_unique($disallowed_keys_array));
  $allowed_words = get_option('ssb_allowed_words', '');
  $allowed_words_array = array_filter(array_map('trim', explode("\n", $allowed_words)));
  $allowed_words_count = count(array_unique($allowed_words_array));
  $last_updated = get_option('ssb_last_keywords_update', 'Never');
  $api_url = 'https://mediafri.com/wp/api/blocked_wp_keywords.php';

  // Fetch stats from cookies if available
  $total_api_keywords_count = isset($_COOKIE['all_remote_keys']) ? intval($_COOKIE['all_remote_keys']) : 0;
  $new_api_keywords_count = isset($_COOKIE['new_remote_keys']) ? intval($_COOKIE['new_remote_keys']) : 0;

  if (!$total_api_keywords_count || !$new_api_keywords_count) {
    // Fetch the actual keywords from the API
    $keywords_response = wp_remote_get($api_url . '?count=1');
    if (!is_wp_error($keywords_response)) {
      $keywords_response_code = wp_remote_retrieve_response_code($keywords_response);
      if ($keywords_response_code === 200) {
        // $total_api_keywords_count = count(json_decode(wp_remote_retrieve_body($keywords_response), true));
        // $new_api_keywords_count = max(0, $total_api_keywords_count - $disallowed_keys_count);

        $total_api_keywords_count = json_decode(wp_remote_retrieve_body($keywords_response), true);
        $allowed_words_array = array_filter(array_map('trim', explode("\n", $allowed_words)));
        $new_api_keywords_count = max(0, $total_api_keywords_count - $disallowed_keys_count);

        // Set cookies using JavaScript
        echo '<script type="text/javascript">
          document.addEventListener("DOMContentLoaded", function() {
            document.cookie = "all_remote_keys=' . esc_js($total_api_keywords_count) . '; path=/; expires=' . esc_js(date('D, d M Y H:i:s', strtotime('+1 week'))) . ' GMT";
            document.cookie = "new_remote_keys=' . esc_js($new_api_keywords_count) . '; path=/; expires=' . esc_js(date('D, d M Y H:i:s', strtotime('+1 week'))) . ' GMT";
          });
        </script>';
      }
    }
  }
  ?>
  <div class="ssb_options_container">
    <div style="flex: 1; border: 1px solid #ddd; padding: 1rem 1.3rem;">
      <h2 style="border-bottom: 1px solid #ddd; margin-top: 5px; padding-bottom: 15px;">Actions</h2>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="ssb_handle_actions" />
        <table class="form-table">
          <tr>
            <th scope="row">Add all pending comments to Spam :</th>
            <td>
              <button type="submit" name="action_type" value="add_pending_to_spam" class="button button-primary ssb_button_warning" style="margin-right:10px">Add to Spam</button>
              <a href="<?php echo admin_url('edit-comments.php?comment_status=moderated'); ?>" class="button button-link">View Pending</a>
              <p>Number of pending comments: <b><?php echo esc_html($pending_count); ?></b></p>
            </td>
          </tr>
          <tr>
            <th scope="row">Empty Spam Now :</th>
            <td>
              <button type="submit" name="action_type" value="empty_spam" class="button button-primary" style="margin-right:10px">Empty Spam</button>
              <a href="<?php echo admin_url('edit-comments.php?comment_status=spam'); ?>" class="button button-link">View Spam</a>
              <p>Number of spam comments: <b><?php echo esc_html($spam_count); ?></b></p>
            </td>
          </tr>
          <tr>
            <th scope="row">Empty Trash Now :</th>
            <td>
              <button type="submit" name="action_type" value="empty_trash" class="button button-primary" style="margin-right:10px">Empty Trash</button>
              <a href="<?php echo admin_url('edit-comments.php?comment_status=trash'); ?>" class="button button-link">View Trash</a>
              <p>Number of trash comments: <b><?php echo esc_html($trash_count); ?></b></p>
            </td>
          </tr>
          <tr>
            <th scope="row">Add Known Spam Keywords :</th>
            <td>
              <button type="button" id="ssb_load_new_keywords" class="button button-primary">Load New Blocked Words</button>
              <div class="ssb_progress_bar" id="ssb_progress_bar" style="display: none;">
                <div id="ssb_progress_bar_inner"></div>
              </div>
              <p style="font-size:13px;font-style:italic;">Adds commonly known spam keywords to the blocked list of this website, from the <b>API endpoint</b>: <a href="https://mediafri.com/wp/api/blocked_wp_keywords.php" target="_blank">https://mediafri.com/wp/api/blocked_wp_keywords</a>.</p>
            </td>
          </tr>
          <tr>
            <th scope="row">Contribute to public list of common spam keywords :</th>
            <td>
              <a href="https://mediafri.com/wp/plugins/spam_super_blocker/contribute.php" target="_blank" class="button button-secondary">Contribute</a>
              <p style="margin-bottom:16px;font-size:13px;font-style:italic;">You too can add your own words to the public blocked list on the <b>API endpoint</b> to help others.</p>
            </td>
          </tr>
        </table>
      </form>
    </div>
    <div style="flex: 1; border: 1px solid #ddd; padding: 1rem 1.3rem;">
      <h2 style="border-bottom: 1px solid #ddd; margin-top: 5px; padding-bottom: 15px;">Stats</h2>
      <h3>Comments on the website:</h3>
      <ul>
        <li>All Comments: <b><?php echo esc_html($all_count); ?></b></li>
        <li>My Comments: <b><?php echo esc_html($my_count); ?></b></li>
        <li>Pending Comments: <b><?php echo esc_html($pending_count); ?></b></li>
        <li>Approved Comments: <b><?php echo esc_html($approved_count); ?></b></li>
        <li>Spam Comments: <b><?php echo esc_html($spam_count); ?></b></li>
        <li>Trash Comments: <b><?php echo esc_html($trash_count); ?></b></li>
        <li>Blocked keywords on this website: <b><?php echo esc_html($disallowed_keys_count); ?></b></li>
        <li>Allowed keywords on this website: <b><?php echo esc_html($allowed_words_count); ?></b></li>
      </ul>
      <h3>Keywords on remote API endpoint:</h3>
      <ul>
        <li>Last updated date: <b><?php echo esc_html($last_updated); ?></b></li>
        <li>All keywords on remote endpoint: <b id="total_api_keywords_count"><?php echo esc_html($total_api_keywords_count); ?></b></li>
        <li>New keywords from remote endpoint: <b id="new_api_keywords_count"><?php echo esc_html($new_api_keywords_count); ?></b>
          <form method="post" style="margin-top:20px;" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="ssb_handle_actions" />
            <input type="hidden" name="action_type" value="refresh_remote" />
            <button type="submit" class="button" style="background-repeat: no-repeat; background-position: center;">Check Remote List</button>
          </form>
        </li>
      </ul>
    </div>
  </div>
  <?php
}

function ssb_render_about_tab() {
  ?>
  <div class="ssb_options_container">
    <div style="flex: 1; border: 1px solid #ddd; padding: 1rem 1.3rem;">
      <h2 style="border-bottom: 1px solid #ddd; margin-top: 5px; padding-bottom: 15px;">
        About Spam Super Blocker
      </h2>
      <div class="wrap">
      <p><strong>Plugin Name:</strong> Spam Super Blocker</p>
      <p><strong>Plugin URI:</strong> <a href="https://mediafri.com/wp/plugins/spam_super_blocker" target="_blank">https://mediafri.com/wp/plugins/spam_super_blocker</a></p>
      <p><strong>Description:</strong> A simple plugin that allows you to manage spam comments collaboratively with ease.</p>
      <hr>
      <h2 style="margin-top:30px;font-size:20px;">Features :</h2>
      <ul>
        <li>- Add spam email and URL values to the "Disallowed Comment Keys" list.</li>
        <li>- Automatically block spam keywords.</li>
        <li>- Auto-clear trash and spam folders based on customizable thresholds.</li>
        <li>- Fetch additional spam keywords from a public API.</li>
      </ul>
      </p>
      <hr>
      <h2 style="margin-top:30px;font-size:20px;">Details & License :</h2>
      <p><strong>Company:</strong> <a href="https://fallaxvision.com" target="_blank">Fallax Vision</a></p>
      <p><strong>Author:</strong> <a href="https://askasjeremy.com" target="_blank">Askas Jeremy</a></p>
      <p><strong>Version:</strong> 1.0.5</p>
      <p><strong>License:</strong> GPLv2 or later</p>
      <p><strong>GitHub Repository:</strong> <a href="https://github.com/Fallax-Vision/wp-spam-super-blocker" target="_blank">https://github.com/Fallax-Vision/wp-spam-super-blocker</a></p>
      <p><strong>Support our work on "Buy Me a Coffee":</strong> <a href="https://buymeacoffee.com/askasjeremy" target="_blank">buymeacoffee.com/askasjeremy</a></p>
      <hr>
      <h2 style="margin-top:30px;font-size:20px;">Terms and Conditions :</h2>
      <p>By using the "<b>Spam Super Blocker</b>" plugin, you agree to the following terms and conditions:</p>
      <ol>
        <li>You are responsible for ensuring that the plugin is used in compliance with all applicable laws and regulations.</li>
        <li>The plugin is provided "as is" without warranty of any kind, express or implied.</li>
        <li>The plugin's developers are not liable for any damages or losses arising from the use or inability to use the plugin.</li>
        <li>You may not modify, distribute, or sell the plugin without the express permission of the plugin's developers.</li>
        <li>The plugin's developers reserve the right to modify or discontinue the plugin at any time without notice.</li>
        <li>The plugin does not collect any personally identifiable information from your website. All data processed by the plugin remains on your server and is not shared with any third parties.</li>
        <li>The plugin is fully FREE and Open-Source. You can check its code on the <a href="https://github.com/Fallax-Vision/wp-spam-super-blocker" target="_blank">GitHub repository</a>.</li>
      </ol>
    </div>
    </div>
    <div style="flex: 1; border: 1px solid #ddd; padding: 1rem 1.3rem;">
      <h2 style="border-bottom: 1px solid #ddd; margin-top: 5px; padding-bottom: 15px;">
        Frequently Asked Questions (FAQs) :
      </h2>
      <h3>1. How does the Spam Super Blocker plugin work?</h3>
      <p>The Spam Super Blocker plugin helps manage spam comments by automatically blocking spam keywords, adding spam email and URL values to the "Disallowed Comment Keys" list, and auto-clearing trash and spam folders based on customizable thresholds. It also fetches additional spam keywords from a public API.</p>

      <h3>2. Can I customize the keywords that are blocked?</h3>
      <p>Yes, you can customize the keywords that are blocked by adding them to the "Allowed Words" list in the plugin settings. This will prevent those keywords from being added to the "Disallowed Comment Keys" list.</p>

      <h3>3. How often does the plugin update the list of blocked keywords?</h3>
      <p>The plugin updates the list of blocked keywords based on the settings you configure. You can set thresholds for when comments in the Trash and Spam folders reach a certain number, triggering the auto-emptying of these folders.</p>

      <h3>4. What happens to the disallowed keywords when I deactivate the plugin?</h3>
      <p>The disallowed keywords list remains intact after deactivation. However, you have the option to delete all settings related to the plugin during uninstallation.</p>

      <h3>5. Can I manually edit the disallowed keywords list?</h3>
      <p>Yes, you can manually edit the disallowed keywords list from the WordPress Discussion settings.</p>

      <h3>6. Does the plugin collect any personal data?</h3>
      <p>No, the plugin does not collect any personally identifiable information from your website. All data processed by the plugin remains on your server and is not shared with any third parties.</p>

      <h3>7. Is the plugin compatible with other spam protection services?</h3>
      <p>The plugin is designed to work independently but can be used alongside other spam protection services. However, it's recommended to test compatibility with your specific setup.</p>

      <h3>8. How can I contribute to the plugin?</h3>
      <p>You can contribute to the plugin by reporting bugs, suggesting new features, or contributing code via the <a href="https://github.com/Fallax-Vision/wp-spam-super-blocker" target="_blank">GitHub repository</a>.</p>

      <h3>9. Is there a way to test the plugin before using it on a live site?</h3>
      <p>Yes, it's recommended to test the plugin on a staging site before deploying it to a live site to ensure it works as expected in your environment.</p>

      <h3>10. What should I do if I encounter an issue with the plugin?</h3>
      <p>If you encounter an issue with the plugin, you can report it on the <a href="https://github.com/Fallax-Vision/wp-spam-super-blocker/issues" target="_blank">GitHub issues page</a> or contact the plugin's developers for support.</p>
    </div>
  </div>
  <?php
}

function ssb_render_roadmap_and_issues_tab() {
  ?>
  <div class="ssb_options_container">
    <div style="flex: 1; border: 1px solid #ddd; padding: 1rem 1.3rem;">
      <h2 style="border-bottom: 1px solid #ddd; margin-top: 5px; padding-bottom: 15px;">
        Roadmap
      </h2>
      <p>The following features are planned for future releases of the Spam Super Blocker plugin:</p>
      <ol class="larger_li">
        <li>Improved spam detection algorithms.</li>
        <li>Stream the list of remote keywords in the logs while they are getting added.</li>
        <li>Active auto-sync with the remote public list to automatically fetch, compare, and add new spam keywords to the blocked-list.</li>
        <li>Integration with other spam protection services.</li>
        <li>Visual stats in the "Stats" section of the "Actions & Stats" tab.</li>
        <li>User-customizable spam filters.</li>
        <li>Support for multilingual spam detection.</li>
        <li>Translate the plugin's interface into multiple languages.</li>
        <li>Allow you to submit your own spam keywords from your website to the shared public list with one click.</li>
      </ol>
    </div>
    <div style="flex: 1; border: 1px solid #ddd; padding: 1rem 1.3rem;">
      <h2 style="border-bottom: 1px solid #ddd; margin-top: 5px; padding-bottom: 15px;">
        Known Issues
      </h2>
      <p>The following issues and improvements are known and will be addressed in future releases:</p>
      <ol class="larger_li">
        <li>Doesn't fetch the last batch of keywords under 50 from the remote API endpoint.</li>
        <li>Occasional false positives in spam detection.</li>
        <li>Performance optimization for large comment volumes.</li>
        <li>Improved user interface for managing spam settings.</li>
        <li>Better handling of edge cases in spam detection.</li>
        <li>Enhanced documentation and user guides.</li>
      </ol>
    </div>
  </div>
  <?php
}

function ssb_render_contributors_tab() {
  ?>
  <!-- ionicons icons for Contributors' Socials -->
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  <h2 style="margin-top:30px;padding:0 2px;font-size:20px;">Contributors :</h2>
  <div class="ssb_contributors_container">
    <div class="ssb_one_contributor">
      <div class="ssb_profile_and_name">
        <div class="ssb_profile">
          <img src="https://secure.gravatar.com/avatar/926d7dd381746119a9ca538c4957c21f?s=96&d=mm&r=g" alt="Askas Jeremy">
        </div>
        <div class="ssb_name_and_username">
          <div class="ssb_wrapper_name_and_username">
            <div class="ssb_name">
              Askas Jeremy
            </div>
            <div class="ssb_role">
              Lead Maintainer
            </div>
          </div>
        </div>
      </div>
      <div class="ssb_socials">
        <div class="ssb_social_icon">
          <a href="https://askasjeremy.com" target="_blank">
            <ion-icon name="globe-outline"></ion-icon>
          </a>
        </div>
        <div class="ssb_social_icon">
          <a href="https://linkedin.com/in/askasjeremy" target="_blank">
            <ion-icon name="logo-linkedin"></ion-icon>
          </a>
        </div>
        <div class="ssb_social_icon">
          <a href="https://dribbble.com/askasjeremy" target="_blank">
            <ion-icon name="logo-dribbble"></ion-icon>
          </a>
        </div>
        <div class="ssb_social_icon">
          <a href="https://instagram.com/askasjeremy" target="_blank">
            <ion-icon name="logo-instagram"></ion-icon>
          </a>
        </div>
        <div class="ssb_social_icon">
          <a href="https://facebook.com/askasjeremy" target="_blank">
          <ion-icon name="logo-facebook"></ion-icon>
          </a>
        </div>
        <div class="ssb_social_icon">
          <a href="https://buymeacoffee.com/askasjeremy" target="_blank">
            <ion-icon name="cafe-outline"></ion-icon>
          </a>
        </div>
      </div>
    </div>
  </div>
  <?php
}

function ssb_get_active_tab() {
  return isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'actions_stats';
}

add_action('admin_menu', function () {
  add_menu_page(
    'Spam Super Blocker',
    'Spam Super Blocker',
    'manage_options',
    'spam-super-blocker',
    'ssb_render_settings_page',
    'dashicons-shield-alt'
  );
});

add_action('admin_init', function () {
  register_setting('ssb_options_group', 'ssb_allowed_words', 'sanitize_textarea_field');
  register_setting('ssb_options_group', 'ssb_block_name');
  register_setting('ssb_options_group', 'ssb_block_email');
  register_setting('ssb_options_group', 'ssb_block_url');
});

add_action('update_option_ssb_allowed_words', 'ssb_update_allowed_words', 10, 2);
function ssb_update_allowed_words($old_value, $new_value) {
  $disallowed_keys = get_option('disallowed_keys', '');
  $disallowed_keys_array = array_filter(array_map('trim', explode("\n", $disallowed_keys)));
  $allowed_words_array = array_filter(array_map('trim', explode("\n", $new_value)));

  $updated_keys_array = array_diff($disallowed_keys_array, $allowed_words_array);
  update_option('disallowed_keys', implode("\n", $updated_keys_array));
}
