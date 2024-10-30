<?php
/*
Plugin Name: Katalogportal PDF Sync
Plugin URI: http://www.colbe.ch
Description: Allow to create PDF Flipbooks with the http://www.katalogportal.ch service.
Version: 1.0.0
Author: Rouh Mehdi
Author URI: http://www.katalogportal.ch
Text Domain: katalogportal

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation;

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
*/

define( 'katalogportal_VERSION', '1.0.0' );
define( 'katalogportal_URL', plugins_url( '', __FILE__ ) );
define( 'katalogportal_DIR', dirname( __FILE__ ) );
define( 'KATALOGPORTAL_KATALOG_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

if ( is_admin() ) {
	require( katalogportal_DIR . '/inc/class.admin.php');
}

// Activate Katalogportal PDF Sync
register_activation_hook( __FILE__, 'katalogportal_Install' );

// Init Katalogportal PDF Sync
function katalogportal_Init() {
	global $katalogportal, $katalogportal_options;
	$katalogportal_options = get_option ( 'katalogportal_options' );
	// Admin
	if ( class_exists( 'katalogportal_Admin' ) ) {
		$katalogportal['admin'] = new katalogportal_Admin();
	}
}

function katalogportal_Install() {
	// Enable default features on plugin activation
	$katalogportal_options = get_option ( 'katalogportal_options' );

	if ( empty( $katalogportal_options ) ) {
		update_option( 'katalogportal_options', array( 
			'katalogportal_username' => '',
			'katalogportal_key' => '',
			'katalogportal_userid' => '',			
		) );
	}
}

add_action( 'plugins_loaded', 'katalogportal_Init' );

function hook_katalogportal_javascript()
{
	wp_enqueue_script( 'tagesmenue-colorbox-script', katalogportal_URL .'/js/colorbox/jquery.colorbox-min.js', array('jquery'), '1.6.4' );
	wp_enqueue_style('tagesmenue-colorbox-style', katalogportal_URL .'/js/colorbox/css/colorbox.css', array(), '1.6.4', 'all');
	wp_enqueue_script( 'katalogportal-js', katalogportal_URL .'/js/katalogportal.js', array('jquery'), '1.0.0' );
	wp_add_inline_script( 'katalogportal-js', 
		'	jQuery(document).ready(function(){' .
		'		jQuery(".iframe").colorbox({iframe:true, width:"90%", height:"90%"});' .
		'	});'	
	);		
	echo $output;
}
add_action('wp_footer','hook_katalogportal_javascript');

function katalogportal_katalog_scripts( $hook ) {
	wp_enqueue_style( 'katalogportal-katalog-admin', KATALOGPORTAL_KATALOG_URL . '/css/admin.css', array(), '1.4.0' );
	wp_enqueue_media();
	wp_enqueue_script( 'katalogportal-katalog-admin', KATALOGPORTAL_KATALOG_URL . '/js/adminK.js', array( 'jquery' ), '1.4.0' );
}
add_action( 'admin_enqueue_scripts', 'katalogportal_katalog_scripts' );