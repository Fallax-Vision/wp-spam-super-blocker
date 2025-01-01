<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

/**
 * Add keywords to the disallowed comment keys list.
 *
 * @param array $keywords Array of keywords to add.
 * @return int The number of new keywords added.
 */
function spam_super_blocker_add_to_disallowed_keys( $keywords ) {
  $current_keys = get_option( 'disallowed_keys', '' );
  $current_keys_array = array_filter( array_map( 'trim', explode( "\n", $current_keys ) ) );

  // Identify new keywords
  $new_keywords = array_diff( $keywords, $current_keys_array );

  if ( ! empty( $new_keywords ) ) {
    $updated_keys_array = array_merge( $current_keys_array, $new_keywords );
    update_option( 'disallowed_keys', implode( "\n", $updated_keys_array ) );
  }

  return count( $new_keywords );
}

/**
 * Validate numeric input.
 *
 * @param mixed $input The input value.
 * @return int|null The validated integer or null if invalid.
 */
function spam_super_blocker_validate_numeric_input( $input ) {
  return is_numeric( $input ) ? intval( $input ) : null;
}
