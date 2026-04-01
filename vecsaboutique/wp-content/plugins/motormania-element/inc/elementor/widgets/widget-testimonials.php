<?php 
namespace Elementor;
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Title
class motormania_Widget_Testimonials extends Widget_Base {
 
   public function get_name() {
      return 'testimonials';
   }
 
   public function get_title() {
      return esc_html__( 'Testimonials', 'motormania' );
   }
 
   public function get_icon() { 
        return 'eicon-testimonial';
   }
 
   public function get_categories() {
      return [ 'motormania-elements' ];
   }

   protected function register_controls() {

      $this->start_controls_section(
         'testimonials_section',
         [
            'label' => esc_html__( 'Testimonials', 'motormania' ),
            'type' => Controls_Manager::SECTION,
         ]
      );

      $this->add_control(
        'text_color',
        [
          'label' => __( 'Text Color', 'motormania' ),
          'type' => \Elementor\Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} h2, {{WRAPPER}} span, {{WRAPPER}} p ' => 'color: {{VALUE}}',
          ]
        ]
      );

      $repeater = new \Elementor\Repeater();

      $repeater->add_control(
         'image',
         [
            'label' => __( 'Choose Photo', 'motormania' ),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'default' => [
               'url' => \Elementor\Utils::get_placeholder_image_src(),
            ],
         ]
      );
      
      $repeater->add_control(
         'name',
         [
            'label' => __( 'Name', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            
         ]
      );

      $repeater->add_control(
         'designation',
         [
            'label' => __( 'Designation', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT
         ]
      );

      $repeater->add_control(
         'testimonial',
         [
            'label' => __( 'Testimonial', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXTAREA
         ]
      );

      
      $repeater->add_control(
         'rating',
         [
            'label' => __( 'Rating', 'appnova' ),
            'type' => Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
              'range' => [
                '%' => [
                  'min' => 1,
                  'max' => 5,
                  'step' => 1,
                ]
              ],
              'default' => [
                'unit' => '%',
                'size' => 5,
              ],
         ]
      );

      $this->add_control(
         'testimonial_list',
         [
            'label' => __( 'Testimonial List', 'motormania' ),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'title_field' => '{{{name}}}',

         ]
      );
      
      $this->end_controls_section();

   }

   protected function render( $instance = [] ) {
 
      // get our input from the widget settings.       
      $settings = $this->get_settings_for_display(); ?>

      <div class="row align-items-center">
        <div class="col-lg-12 col-md-12">
            <div class="testimonials">
              <?php foreach (  $settings['testimonial_list'] as $testimonial_single ): ?>
                <div class="testimonial">
                  <i class="fad fa-quote-right"></i>
                  <div class="testimonial-img">
                    <img src="<?php echo esc_url( $testimonial_single['image']['url'] ); ?>" alt="<?php echo esc_html($testimonial_single['name']); ?>">
                  </div>
         
                  <div class="testimonial-content">
                    <p><?php echo esc_html($testimonial_single['testimonial']); ?></p>
                    <div class="testi-bottom">
                      <div class="client-info">
                          <h4><?php echo esc_html($testimonial_single['name']); ?></h4>
                          <span><?php echo esc_html($testimonial_single['designation']); ?></span>
                      </div>
                      <ul class="list-inline mb-0">
                       <?php for ($i=0; $i < $testimonial_single['rating']['size']; $i++) { ?>
                         <li class="list-inline-item"><i class="fa fa-star"></i></li>
                       <?php } ?>
                      </ul>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
        </div>
      </div>

   <?php } 
 
}

Plugin::instance()->widgets_manager->register_widget_type( new motormania_Widget_Testimonials );