<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$general_settings_options = apply_filters( 'fw_ext_seo_general_setting_options', array() );
$general_settings         = apply_filters( 'fw_ext_seo_general_settings', array() );
$tabs                     = apply_filters( 'fw_ext_seo_settings_options', array() );

if ( empty( $general_settings_options ) && empty( $general_settings ) && empty( $tabs ) ) {
	$options = array();

	return;
}

$options = array(
	'general-tab' => array(
		'title'   => __( 'General', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'general-settings' => array(
				'title'   => __( 'General Settings', 'fw' ),
				'type'    => 'box',
				'options' => array(
					$general_settings_options
				)
			),
			$general_settings
		),

	),
	$tabs
);