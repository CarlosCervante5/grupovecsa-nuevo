<?php 
namespace Elementor;
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// iconbox item
class motormania_Widget_IconBox extends Widget_Base {
 
   public function get_name() {
      return 'icon_item';
   }
 
   public function get_title() {
      return esc_html__( 'Icon Box', 'motormania' );
   }
 
   public function get_icon() { 
        return 'eicon-facebook-comments';
   }
 
   public function get_categories() {
      return [ 'motormania-elements' ];
   }
   protected function register_controls() {
      $this->start_controls_section(
         'iconbox_section',
         [
            'label' => esc_html__( 'Icon Box', 'motormania' ),
            'type' => Controls_Manager::SECTION,
         ]
      );

      $this->add_control(
         'icon',
         [
            'label' => __( 'Choose icon', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'fal fa-user-headset',
         ]
      );

      $this->add_control(
         'title',
         [
            'label' => __( 'Title', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Support 24/7','motormania'),
         ]
      );
      $this->add_control(
         'text',
         [
            'label' => __( 'Text', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('Call us anytime','motormania'),
         ]
      );

      $this->add_control(
        'text_color',
        [
          'label' => __( 'Text Color', 'motormania' ),
          'type' => \Elementor\Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} h5, {{WRAPPER}} p, {{WRAPPER}} i ' => 'color: {{VALUE}}',
          ]
        ]
      );
      
      $this->end_controls_section();
   }
   protected function render( $instance = [] ) {
 
      // get our input from the widget settings.
       
      $settings = $this->get_settings_for_display(); ?>

         <div class="iconbox-item">
            <div><i class="fal <?php echo esc_attr( $settings['icon'] ); ?>"></i></div>
            <div>
               <h5><?php echo esc_html( $settings['title'] ); ?></h5>
               <p><?php echo esc_html( $settings['text'] ); ?></p>
            </div>
         </div>

      <?php
   }
 
}
Plugin::instance()->widgets_manager->register_widget_type( new motormania_Widget_IconBox );