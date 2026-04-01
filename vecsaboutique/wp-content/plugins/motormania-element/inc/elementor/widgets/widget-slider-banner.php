<?php 
namespace Elementor;
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Banner
class motormania_Widget_Slider_Banner extends Widget_Base {
 
   public function get_name() {
      return 'slider_banner';
   }
 
   public function get_title() {
      return esc_html__( 'Slider Banner', 'motormania' );
   }
 
   public function get_icon() { 
        return 'eicon-banner';
   }
 
   public function get_categories() {
      return [ 'motormania-elements' ];
   }

   protected function register_controls() {

      $this->start_controls_section(
         'slider_banner_section',
         [
            'label' => esc_html__( 'Slider Banner', 'motormania' ),
            'type' => Controls_Manager::SECTION,
         ]
      );

      $banner = new \Elementor\Repeater();

      $banner->add_control(
        'image',
        [
          'label' => __( 'Choose Image', 'motormania' ),
          'type' => \Elementor\Controls_Manager::MEDIA,
          'default' => [
            'url' => \Elementor\Utils::get_placeholder_image_src(),
          ],
        ]
      );


      $banner->add_control(
        'product_image',
        [
          'label' => __( 'Product Image', 'motormania' ),
          'type' => \Elementor\Controls_Manager::MEDIA,
          'default' => [
            'url' => \Elementor\Utils::get_placeholder_image_src(),
          ],
        ]
      );

      $banner->add_control(
         'title',
         [
            'label' => __( 'Title', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('Driving a motorcycle is like flying','motormania' )
         ]
      );


      $banner->add_control(
         'description',
         [
            'label' => __( 'Description', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('I really love to ride my motorcycle. When I want to just get away and be by myself and clear my head, that\'s what I do.','motormania' )
         ]
      );

      $banner->add_control(
         'button_url',
         [
            'label' => __( 'Button url', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '#'
         ]
      );

      $banner->add_control(
         'button_text',
         [
            'label' => __( 'Button text', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Watch Video','motormania' )
         ]
      );

      $this->add_control(
         'banners',
         [
            'label' => __( 'Banners', 'medicament' ),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $banner->get_controls(),
            'title_field' => 'Banner item',
            'title_field' => '{{{ title }}}',
         ]
      );

      $this->add_control(
        'text_color',
        [
          'label' => __( 'Text Color', 'motormania' ),
          'type' => \Elementor\Controls_Manager::COLOR,
        ]
      );

      $this->end_controls_section();

   }

  protected function render( $instance = [] ) {
 
      // get our input from the widget settings.
       
      $settings = $this->get_settings_for_display(); ?>

      <div class="banner-slider">
        <?php foreach (  $settings['banners'] as $banner ) { ?>
          <section class="banner style-3" style="background: url(<?php echo esc_url( $banner['image']['url'] ) ?>);">
            <div class="container">
              <div class="row">
                <div class="col-lg-6 my-auto">
                  <div class="banner-content">
                    <h1 style="color: <?php echo esc_attr( $settings['text_color']) ?> ">
                      <?php echo esc_html( $banner['title'] ); ?>
                    </h1>
                    <p style="color: <?php echo esc_attr( $settings['text_color']) ?> "><?php echo esc_html($banner['description']); ?></p>
                    <a class="motormania-btn mt-50 pt-3 pb-3" href="<?php echo esc_url( $banner['button_url'] ); ?>"><?php echo esc_html( $banner['button_text'] ); ?></a>
                  </div>
                </div>
                
                <div class="col-lg-6 my-auto">
                  <img src="<?php echo esc_url( $banner['product_image']['url'] ) ?>" alt="<?php echo esc_attr( $banner['title'] ); ?>">
                </div>
              </div>
            </div>
          </section>
        <?php } ?>
      </div>
      

      <?php
   }
 
}

Plugin::instance()->widgets_manager->register_widget_type( new motormania_Widget_Slider_Banner );