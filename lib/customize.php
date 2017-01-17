<?php

/**
 * Customizer additions.
 *
 * @package Genesis BW
 * @author  bobbingwide
 * @link    http://my.bobbingwide.com/themes/genesis-bw/
 * @license GPL2-0+
 */

/**
 * Get default accent color for Customizer.
 *
 * Abstracted here since at least two functions use it.
 *
 * @since 1.0.0
 *
 * @return string Hex color code for accent color.
 */
function genesis_bw_customizer_get_default_accent_color() {
	return '#ff4800';
}

add_action( 'customize_register', 'genesis_bw_customizer_register' );
/**
 * Register settings and controls with the Customizer.
 *
 * @since 1.0.0
 * 
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function genesis_bw_customizer_register() {

	global $wp_customize;

	$images = apply_filters( 'genesis_bw_images', array( '1', '2' ) );

	$wp_customize->add_section( 'genesis-bw-settings', array(
		'description' => __( 'Use the included default images or personalize your site by uploading your own images.<br /><br />The default images are <strong>1800 pixels wide and 500 pixels tall</strong>.', 'genesis-bw' ),
		'title'    => __( 'Front Page Background Images', 'genesis-bw' ),
		'priority' => 35,
	) );

	foreach( $images as $image ){

		$wp_customize->add_setting( $image .'-genesis-bw-image', array(
			'default'  => sprintf( '%s/images/bg-%s.jpg', get_stylesheet_directory_uri(), $image ),
			'type'     => 'option',
		) );

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $image .'-genesis-bw-image', array(
			'label'    => sprintf( __( 'Featured Section %s Image:', 'genesis-bw' ), $image ),
			'section'  => 'genesis-bw-settings',
			'settings' => $image .'-genesis-bw-image',
			'priority' => $image+1,
		) ) );

	}

	$wp_customize->add_setting(
		'genesis_bw_accent_color',
		array(
			'default' => genesis_bw_customizer_get_default_accent_color(),
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'genesis_bw_accent_color',
			array(
				'description' => __( 'Change the default accent color for links, buttons, and more.', 'genesis-bw' ),
			    'label'       => __( 'Accent Color', 'genesis-bw' ),
			    'section'     => 'colors',
			    'settings'    => 'genesis_bw_accent_color',
			)
		)
	);

}
