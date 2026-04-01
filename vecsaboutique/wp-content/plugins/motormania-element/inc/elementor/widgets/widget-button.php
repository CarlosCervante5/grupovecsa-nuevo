<?php 
namespace Elementor;
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Button
class motormania_Widget_Button extends Widget_Base {
 
   public function get_name() {
      return 'button';
   }
 
   public function get_title() {
      return esc_html__( 'Button', 'motormania' );
   }
 
   public function get_icon() { 
        return 'eicon-button';
   }
 
   public function get_categories() {
      return [ 'motormania-elements' ];
   }

   protected function register_controls() {

      $this->start_controls_section(
         'button_section',
         [
            'label' => esc_html__( 'Button', 'motormania' ),
            'type' => Controls_Manager::SECTION,
         ]
      );

      $this->add_control(
         'button_text', [
            'label' => __( 'Button Text', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Learn More','motormania')
         ]
      );

      $this->add_control(
         'button_url', [
            'label' => __( 'Button URL', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '#'
         ]
      );
      
      $this->add_control(
         'align',
         [
            'label' => __( 'Align', 'motormania' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'left',
            'options' => [
               'center'  => __( 'Center', 'motormania' ),
               'left' => __( 'Left', 'motormania' ),
               'right' => __( 'Right', 'motormania' )
            ],
         ]
      );

      $this->add_control(
         'border',
         [
            'label' => __( 'border', 'motormania' ),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __( 'On', 'motormania' ),
            'label_off' => __( 'Off', 'motormania' ),
            'return_value' => 'bordered',
            'default' => 'no',
         ]
      );

      $this->end_controls_section();

   }

   protected function render( $instance = [] ) {
 
      // get our input from the widget settings.
       
      $settings = $this->get_settings_for_display(); ?>

      <div style="text-align: <?php echo esc_attr($settings['align']) ?>">
         <a class="motormania-btn <?php echo esc_attr($settings['border']) ?>" href="<?php echo esc_url( $settings['button_url'] ); ?>">
            <?php echo esc_html( $settings['button_text'] ); ?></a>
      </div>
      <?php
   }
 
}

Plugin::instance()->widgets_manager->register_widget_type( new motormania_Widget_Button );