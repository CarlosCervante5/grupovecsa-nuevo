<?php

if ( ! defined( 'ABSPATH' ) ) exit;


// get posts dropdown
function motormania_get_posts_dropdown_array($args = [], $key = 'ID', $value = 'post_title') {
  $options = [];
  $posts = get_posts($args);
  foreach ((array) $posts as $term) {
    $options[$term->{$key}] = $term->{$value};
  }
  return $options;
}

// get terms dropdown
function motormania_get_terms_dropdown_array($args = [], $key = 'term_id', $value = 'name') {
  $options = [];
  $terms = get_terms($args);

  if (is_wp_error($terms)) {
    return [];
  }

  foreach ((array) $terms as $term) {
    $options[$term->{$key}] = $term->{$value};
  }

  return $options;
}

// get terms dropdown
function motormania_attribute_taxonomies_dropdown_array(){
  $options = [];
  $terms = wc_get_attribute_taxonomies();

  if (is_wp_error($terms)) {
    return [];
  }

  if ( $terms ) {
      foreach ((array) $terms as $tax) {
      if (taxonomy_exists(wc_attribute_taxonomy_name($tax->attribute_name))) {
          $options[$tax->attribute_name] = $tax->attribute_label;
      };
    };
  };

  return $options;
}

function motormania_add_elementor_widget_categories( $elements_manager ) {

	$elements_manager->add_category(
		'motormania-elements',
		[
			'title' => esc_html__( 'MotorMania Elements', 'motormania' ),
			'icon' => 'fa fa-plug',
		]
	);

}
add_action( 'elementor/elements/categories_registered', 'motormania_add_elementor_widget_categories' );

//Elementor init

class motormania_ElementorCustomElement {
 
   private static $instance = null;
 
   public static function get_instance() {
      if ( ! self::$instance )
         self::$instance = new self;
      return self::$instance;
   }
 
   public function init(){
      add_action( 'elementor/widgets/register', array( $this, 'widgets_registered' ) );
   }


   public function widgets_registered() {
 
    // We check if the Elementor plugin has been installed / activated.
    if(defined('ELEMENTOR_PATH') && class_exists('Elementor\Widget_Base')){      
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-accordion.php');
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-ad.php');
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-ajax-search.php');
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-banner.php');
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-slider-banner.php');
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-blog.php');
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-button.php');
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-counter.php');
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-countdown.php');
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-products.php');
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-iconbox.php');
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-product-categories.php');
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-products-carousel.php');
         include_once(plugin_dir_path( __FILE__ ).'/widgets/widget-testimonials.php');
      }
	}

}
 
motormania_ElementorCustomElement::get_instance()->init();