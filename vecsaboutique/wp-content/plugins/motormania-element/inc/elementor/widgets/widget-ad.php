<?php 
namespace Elementor;
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Ad Banner
class motormania_Widget_ad_banner extends Widget_Base {
 
   public function get_name() {
      return 'ad_banner';
   }
 
   public function get_title() {
      return esc_html__( 'Ad Banner', 'motormania' );
   }
 
   public function get_icon() { 
        return 'eicon-call-to-action';
   }
 
   public function get_categories() {
      return [ 'motormania-elements' ];
   }

   protected function register_controls() {

      $this->start_controls_section(
         'ad_banner_section',
         [
            'label' => esc_html__( 'Ad Banner', 'motormania' ),
            'type' => Controls_Manager::SECTION,
         ]
      );
      
      $this->add_control(
         'layout',
         [
            'label' => __( 'Layout', 'motormania' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'horizontal',
            'options' => [
               'horizontal'  => __( 'Horizontal', 'motormania' ),
               'vertical' => __( 'Vertical', 'motormania' )
            ],
         ]
      );

      $this->add_control(
         'align',
         [
            'label' => __( 'Alignment', 'motormania' ),
            'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
               'left' => [
                  'title' => __( 'Left', 'motormania' ),
                  'icon' => 'fa fa-align-left',
               ],
               'center' => [
                  'title' => __( 'Center', 'motormania' ),
                  'icon' => 'fa fa-align-center',
               ],
               'right' => [
                  'title' => __( 'Right', 'motormania' ),
                  'icon' => 'fa fa-align-right',
               ],
            ],
            'default' => 'left',
            'toggle' => true
         ]
      );

      $this->add_responsive_control(
      'title_padding',
        [
          'label' => __( 'Padding', 'motormania' ),
          'type' => \Elementor\Controls_Manager::DIMENSIONS,
          'size_units' => [ 'px', 'em', '%' ],
          'selectors' => [
            '{{WRAPPER}} .adbanner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'image',
        [
          'label' => __( 'Choose Image', 'motormania' ),
          'type' => \Elementor\Controls_Manager::MEDIA,
          'default' => [
            'url' => \Elementor\Utils::get_placeholder_image_src(),
          ],
        ]
      );

      $this->add_control(
         'title',
         [
            'label' => __( 'Title', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __( 'Body Parts', 'motormania' )
         ]
      );

      $this->add_control(
         'description',
         [
            'label' => __( 'Description', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __( 'For Any Vehicle', 'motormania' )
         ]
      );

      $this->add_control(
        'text_color',
        [
          'label' => __( 'Text Color', 'motormania' ),
          'type' => \Elementor\Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} h2, {{WRAPPER}} h3, {{WRAPPER}} p ' => 'color: {{VALUE}}',
          ]
        ]
      );

      $links = new \Elementor\Repeater();

      $links->add_control(
         'link_text', [
            'label' => __( 'Link Text', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __( 'Accessories', 'motormania' )
         ]
      );

      $links->add_control(
         'link_url', [
            'label' => __( 'Link URL', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '#',
         ]
      );

      $this->add_control(
         'links',
         [
            'label' => __( 'Links', 'motormania' ),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $links->get_controls(),
            'title_field' => 'link Item',
            'condition' => [
               'layout' => ['vertical']
            ],
            'default' => [
               [
                  'link_text' => __( 'Accessories', 'motormania' ),
                  'link_url' => '#'
               ],
               [
                  'link_text' => __( 'Timing Belts', 'motormania' ),
                  'link_url' => '#'
               ],
               [
                  'link_text' => __( 'Engine Mounts', 'motormania' ),
                  'link_url' => '#'
               ]
            ],
            'title_field' => '{{{ link_text }}}',
         ]
      );

      $this->add_control(
         'btn_text',
         [
            'label' => __( 'Button', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __( 'Shop Now', 'motormania' )
         ]
      );

      $this->add_control(
         'btn_url',
         [
            'label' => __( 'Button URL', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '#'
         ]
      );
      
      $this->end_controls_section();

   }

   protected function render( $instance = [] ) {
 
      // get our input from the widget settings.
       
      $settings = $this->get_settings_for_display(); ?>

      <?php if ($settings['layout'] == 'horizontal') { ?>
        
      <section class="adbanner horizontal" style="background-image: url(<?php echo esc_url($settings['image']['url']); ?>);">
        <div class="container">
          <div class="row justify-content-between">
            <div class="col-md-12 text-<?php echo esc_attr( $settings['align'] ); ?>">
              <p><?php echo esc_html($settings['description']); ?></p>
              <h2 class="mb-5"><?php echo esc_html($settings['title']); ?></h2>
              <a class="motormania-btn" href="<?php echo esc_url($settings['btn_url']); ?>"><?php echo esc_html($settings['btn_text']); ?></a>
            </div>
          </div>
        </div>        
      </section>

      <?php } elseif($settings['layout'] == 'vertical') { ?>

      <section class="adbanner vertical" style="background-image: url(<?php echo esc_url($settings['image']['url']); ?>);">
        <div class="container">
          <div class="row justify-content-between">
            <div class="text-<?php echo esc_attr( $settings['align'] ); ?>">
              <h3><?php echo esc_html($settings['title']); ?></h3>
              <p class="mb-4"><?php echo esc_html($settings['description']); ?></p>
              <?php if ($settings['links']): ?>
                <ul class="list-unstyled mb-4">
                  <?php foreach ($settings['links'] as $key => $link): ?>
                    <li><a href="<?php echo esc_url($link['link_url']); ?>"><?php echo esc_html($link['link_text']); ?></a></li>
                  <?php endforeach ?>
                </ul>
              <?php endif ?>
              
              <a class="motormania-btn" href="<?php echo esc_url($settings['btn_url']); ?>"><?php echo esc_html($settings['btn_text']); ?></a>
            </div>
          </div>
        </div>        
      </section>

      <?php } ?>
      
      <?php
   }
 
}

Plugin::instance()->widgets_manager->register_widget_type( new motormania_Widget_ad_banner );