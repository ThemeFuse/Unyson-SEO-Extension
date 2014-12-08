<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

/**
 * Posts types that you want to exclude from titles and meta settings
 */
$cfg['excluded_post_types'] = array( 'attachment' );

/**
 * Taxonomies that you want to exclude from titles and meta settings.
 */
$cfg['excluded_taxonomies'] = array( 'post_tag' );