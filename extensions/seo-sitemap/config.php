<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Sitemap extension configuration file
 */

$cfg = array();

/**
 * Search engines where to report about the sitemap existence.
 * By default the extension supports only Google and Bing.
 */
$cfg['search_engines'] = array( 'google', 'bing' );

/**
 * Exclude post types from sitemap indexing.
 */
$cfg['excluded_post_types']  = array( 'attachment' );

/**
 * Exclude taxonomies from sitemap indexing.
 */
$cfg['excluded_taxonomies']  = array( 'post_tag' );

/**
 * Setup the URL frequency and priority for each post_type, taxonomy and the homepage
 */
$cfg['url_settings'] = array(
	'home'  => array(
		'priority'  => 1,
		'frequency' => 'daily',
	),
	'posts' => array(
		'priority'  => 0.6,
		'frequency' => 'daily',
		'type'      => array(
			'page' => array(
				'priority'  => 0.5,
				'frequency' => 'weekly',
			)
		)
	),
	'taxonomies'     => array(
		'priority'  => 0.4,
		'frequency' => 'weekly',
		'type'  => array(
			'post_tag'  => array(
				'priority'  => 0.3,
				'frequency' => 'weekly',
			)
		)
	)
);