<?php

// Enqueue script
function motormania_plugin_enqueue_script() {

	// CSS
	wp_enqueue_style('motormania-plugns', plugin_dir_url( __FILE__ ) . '../assets/css/plugins.css');
	wp_enqueue_style('motormania-plugn', plugin_dir_url( __FILE__ ) . '../assets/css/plugin.css');

	// JS
	wp_enqueue_script( 'motormania-plugins', plugin_dir_url( __FILE__ ) . '../assets/js/plugins.js', array('jquery'), wp_get_theme()->get( 'Version' ), true );
	wp_enqueue_script( 'motormania-plugin', plugin_dir_url( __FILE__ ) . '../assets/js/plugin.js', array('jquery','motormania-main'), wp_get_theme()->get( 'Version' ), true );
	wp_localize_script( 'motormania-plugin', 'motormaniaPluginAjaxObj', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
}
add_action('wp_enqueue_scripts', 'motormania_plugin_enqueue_script');