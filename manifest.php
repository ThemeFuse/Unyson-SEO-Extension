<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = array();

$manifest['name']        = __( 'SEO', 'fw' );
$manifest['description'] = __(
	'This extension will enable you to have a fully optimized WordPress website'
	.' by adding optimized meta titles, keywords and descriptions.',
	'fw'
);
$manifest['github_repo'] = 'https://github.com/ThemeFuse/Unyson-SEO-Extension';
$manifest['uri'] = 'http://manual.unyson.io/en/latest/extension/seo/index.html#content';
$manifest['author'] = 'ThemeFuse';
$manifest['author_uri'] = 'http://themefuse.com/';
$manifest['version'] = '1.0.11';
$manifest['display'] = true;
$manifest['standalone'] = true;

$manifest['github_update'] = 'ThemeFuse/Unyson-SEO-Extension';
