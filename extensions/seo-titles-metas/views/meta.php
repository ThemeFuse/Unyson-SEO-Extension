<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * HTML <meta> tag view
 *
 * View supports 2 parameters: $name, $content
 *
 * @var $name , meta tag name attribute value
 * @var $content , meta tag content attribute value
 */

?>

<meta name="<?php echo esc_attr($name) ?>" content="<?php echo esc_attr($content) ?>"/>