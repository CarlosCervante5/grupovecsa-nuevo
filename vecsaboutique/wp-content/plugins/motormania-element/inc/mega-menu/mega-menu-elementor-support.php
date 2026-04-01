<?php

namespace Elementor;

// elementor megamenu shortcode
function motormania_megamenu_shortcode_elementor($atts){

    if(!class_exists('Elementor\Plugin')){
        return '';
    }
    if(!isset($atts['id']) || empty($atts['id'])){
        return '';
    }

    $post_id = $atts['id'];

    $response = Plugin::instance()->frontend->get_builder_content_for_display($post_id);
    return $response;
}
add_shortcode('megamenu_shortcode','Elementor\motormania_megamenu_shortcode_elementor');

// [megamenu_shortcode id="ID"]
// echo do_shortcode('[megamenu_shortcode id="ID"]');

