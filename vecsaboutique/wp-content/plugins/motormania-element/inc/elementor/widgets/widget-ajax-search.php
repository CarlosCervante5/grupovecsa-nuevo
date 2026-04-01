<?php 
namespace Elementor;
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// search
class motormania_Widget_search extends Widget_Base {
 
   public function get_name() {
      return 'search';
   }
 
   public function get_title() {
      return esc_html__( 'Search', 'motormania' );
   }
 
   public function get_icon() { 
        return 'eicon-search-bold';
   }
 
   public function get_categories() {
      return [ 'motormania-elements' ];
   }

   protected function register_controls() {

      $this->start_controls_section(
         'search_section',
         [
            'label' => esc_html__( 'Search', 'motormania' ),
            'type' => Controls_Manager::SECTION,
         ]
      );

      $this->end_controls_section();

   }

   protected function render( $instance = [] ) {
 
    // get our input from the widget settings.       
    $settings = $this->get_settings_for_display(); ?>
      <form class="ajax-search-form" action="<?php echo esc_url(home_url( '/' )); ?>">
         <input type="text" name="s" id="keyword" placeholder="<?php echo esc_attr__( 'Search', 'motormania' ); ?>">
         <button type="submit"><i class="fal fa-search"></i></button>
         <input type="hidden" name="post_type" value="product" />
         <div id="datafetch"></div>
      </form>

      <?php
   }
}

Plugin::instance()->widgets_manager->register_widget_type( new motormania_Widget_search );