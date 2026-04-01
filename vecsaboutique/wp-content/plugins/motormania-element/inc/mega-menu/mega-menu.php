<?php 

function motormania_megamenu_scripts() {

	wp_enqueue_style( 'motormania-mega-menu', plugin_dir_url( __FILE__ ) . '/assets/css/mega-menu.css');

	wp_enqueue_script( 'motormania-mega-menu', plugin_dir_url( __FILE__ ) . '/assets/js/mega-menu.js' , array('jquery'), wp_get_theme()->get( 'Version' ), true );
}

add_action( 'wp_enqueue_scripts', 'motormania_megamenu_scripts' );

// Includes file
require_once dirname( __FILE__ ) . '/mega-menu-elementor-support.php';
require_once dirname( __FILE__ ) . '/mega-menu-walker.php';