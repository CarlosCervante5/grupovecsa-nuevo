<?php 
namespace Elementor;
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Banner
class motormania_Widget_Search_Banner extends Widget_Base {
 
   public function get_name() {
      return 'search_banner';
   }
 
   public function get_title() {
      return esc_html__( 'Search Banner', 'motormania' );
   }
 
   public function get_icon() { 
        return 'eicon-slider-video';
   }
 
   public function get_categories() {
      return [ 'motormania-elements' ];
   }

   protected function register_controls() {

      $this->start_controls_section(
         'search_banner_section',
         [
            'label' => esc_html__( 'Search Banner', 'motormania' ),
            'type' => Controls_Manager::SECTION,
         ]
      );

      $this->add_control(
         'style',
         [
            'label' => __( 'Style', 'motormania' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'style-1',
            'options' => [
               'style-1'  => __( 'Style 1', 'motormania' ),
               'style-2' => __( 'Style 2', 'motormania' )
            ],
         ]
      );

      $this->add_control(
        'banner_image',
        [
          'label' => __( 'Banner image', 'motormania' ),
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
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('Find Parts For Your Vehicle','motormania' )
         ]
      );

      $this->add_control(
         'colored_title',
         [
            'label' => __( 'Colored Title', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('Your Vehicle','motormania' ),
            'condition' => [
              'style' => ['style-2']
            ]
         ]
      );

      $this->add_control(
         'description',
         [
            'label' => __( 'Description', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('Over hundreds of brands and tens of thousands of parts','motormania' )
         ]
      );

      $this->add_control(
        'text_color',
        [
          'label' => __( 'Text Color', 'motormania' ),
          'type' => \Elementor\Controls_Manager::COLOR
        ]
      );

      $this->add_control(
         'search_display',
         [
            'label' => __( 'Search', 'motormania' ),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __( 'On', 'motormania' ),
            'label_off' => __( 'Off', 'motormania' ),
            'return_value' => 'on',
            'default' => 'on'
         ]
      );

      $this->add_control(
         'select_one',
         [
            'label' => __( 'Select One', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Riding Style','motormania' ),
            'condition' => [
              'search_display' => ['on']
            ]
         ]
      );

      $this->add_control(
         'select_two',
         [
            'label' => __( 'Select Two', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Model','motormania' ),
            'condition' => [
              'search_display' => ['on']
            ]
         ]
      );

      $this->add_control(
         'select_three',
         [
            'label' => __( 'Select Three', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Year','motormania' ),
            'condition' => [
              'search_display' => ['on']
            ]
         ]
      );

      $this->add_control(
         'select_four',
         [
            'label' => __( 'Select Four', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Brand','motormania' ),
            'condition' => [
              'search_display' => ['on']
            ]
         ]
      );

      $this->add_control(
         'button',
         [
            'label' => __( 'Button Text', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Shop Now','motormania' ),
            'condition' => [
              'style' => ['style-2']
            ]
         ]
      );

      $this->add_control(
         'button_url',
         [
            'label' => __( 'Button URL', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '#',
            'condition' => [
              'style' => ['style-2']
            ]
         ]
      );
            
      $this->end_controls_section();

   }

  protected function render( $instance = [] ) {
 
      // get our input from the widget settings.
       
      $settings = $this->get_settings_for_display(); ?>

        <?php if ($settings['style'] == 'style-1') { ?>
          <section class="banner" style="background: url(<?php echo esc_url($settings['banner_image']['url']) ?>);">
            <div class="container">
              <div class="row">
                <div class="col-lg-12">
                  <div class="banner-content">
                    <h1 style="color: <?php echo esc_attr( $settings['text_color']) ?> ">
                      <?php echo esc_html( $settings['title'] ); ?>
                    </h1>
                    <p style="color: <?php echo esc_attr( $settings['text_color']) ?> "><?php echo esc_html($settings['description']); ?></p>
                  </div>
                  <?php if ( $settings['search_display'] == 'on' ){ ?>
                    <div class="brands_form">
                      <form class="motormania-select-search" method="GET" action="<?php echo esc_url(home_url( '/' )); ?>">
                        <ul class="list-inline">
                          <li class="list-inline-item">
                            <select name="product_cat" id="select-one">
                              <option value='-1' disabled selected><?php echo esc_html($settings['select_one']) ?></option>
                              <?php $product_taxonomy = get_terms(array(
                                'taxonomy' => 'product_cat',
                                'hide_empty' => true,
                                'parent' => 0
                              )); 

                              foreach ($product_taxonomy as $key => $taxonomy) { ?>
                                <option value="<?php echo esc_attr( $taxonomy->slug ) ?>" data-id="<?php echo $taxonomy->term_id ?>">
                                  <?php echo esc_attr( $taxonomy->name ) ?>
                                  <?php if ($taxonomy->count > 0){
                                    echo ' (' . esc_attr($taxonomy->count) . ')';
                                  } ?>
                                </option>
                              <?php } ?>
                            </select>
                          </li>
                          <li class="list-inline-item">
                            <select name="product_cat" id="select-two">
                              <option value="-1" disabled selected><?php echo esc_html($settings['select_two']) ?></option>
                            </select>
                          </li>
                          <li class="list-inline-item">
                            <select name="product_cat" id="select-three">
                              <option value="-1" disabled selected><?php echo esc_html($settings['select_three']) ?></option>
                            </select>
                          </li>
                          <li class="list-inline-item">
                            <select name="product_cat" id="select-four">
                              <option value="-1" disabled selected><?php echo esc_html($settings['select_four']) ?></option>
                            </select>
                          </li>
                          <li class="list-inline-item">
                            <input type="submit" value="<?php echo esc_attr__('Search', 'motormania') ?>">
                            <input type="hidden" name="post_type" value="product" />
                          </li>
                        </ul>
                      </form>
                    </div>
                  <?php } ?>
                </div>              
              </div>
            </div>
          </section>
        <?php } elseif($settings['style'] == 'style-2') { ?>
          <section class="banner <?php echo esc_attr($settings['style']) ?>" style="background: url(<?php echo esc_url($settings['banner_image']['url']) ?>);">
            <div class="container">
              <div class="row">
                <div class="col-lg-6 my-auto">
                  <div class="banner-content mb-lg-0">
                    <h1 style="color: <?php echo esc_attr( $settings['text_color']) ?> ">
                      <?php echo esc_html( $settings['title'] ); ?><span><?php echo esc_html( $settings['colored_title'] ); ?></span>
                    </h1>
                    <p style="color: <?php echo esc_attr( $settings['text_color']) ?> "><?php echo esc_html($settings['description']); ?></p>
                      <?php if ($settings['button_url']): ?>
                        <a class="motormania-btn mt-5" href="<?php echo esc_url($settings['button_url']); ?>"><?php echo esc_html($settings['button']); ?></a>
                      <?php endif ?>                    
                  </div>
                </div>
                <div class="col-lg-4 offset-lg-2 my-auto">
                <?php if ( $settings['search_display'] == 'on' ){ ?>
                  <div class="brands_form">
                    <form class="motormania-select-search" method="GET" action="<?php echo esc_url(home_url( '/' )); ?>">
                      <div class="row">
                        <div class="col-lg-12">
                          <select name="product_cat" id="select-one">
                            <option value='-1' disabled selected><?php echo esc_html($settings['select_one']) ?></option>
                            <?php $product_taxonomy = get_terms(array(
                              'taxonomy' => 'product_cat',
                              'hide_empty' => true,
                              'parent' => 0
                            )); 

                            foreach ($product_taxonomy as $key => $taxonomy) { ?>
                              <option value="<?php echo esc_attr( $taxonomy->slug ) ?>" data-id="<?php echo $taxonomy->term_id ?>">
                                <?php echo esc_attr( $taxonomy->name ) ?>
                                <?php if ($taxonomy->count > 0){
                                  echo ' (' . esc_attr($taxonomy->count) . ')';
                                } ?>
                              </option>
                            <?php } ?>
                          </select>
                        </div>
                        <div class="col-lg-12">
                          <select name="product_cat" id="select-two">
                            <option value="-1" disabled selected><?php echo esc_html($settings['select_two']) ?></option>
                          </select>
                        </div>
                        <div class="col-lg-6">
                          <select name="product_cat" id="select-three">
                            <option value="-1" disabled selected><?php echo esc_html($settings['select_three']) ?></option>
                          </select>
                        </div>
                        <div class="col-lg-6">
                          <select name="product_cat" id="select-four">
                            <option value="-1" disabled selected><?php echo esc_html($settings['select_four']) ?></option>
                          </select>
                        </div>
                        <div class="col-lg-12">
                          <input type="submit" value="<?php echo esc_attr__('Search', 'motormania') ?>">
                          <input type="hidden" name="post_type" value="product" />
                        </div>
                      </div>
                    </form>
                  </div>
                <?php } ?>
              </div>              
              </div>
            </div>
          </section>
        <?php } ?>
      <?php
   }
 
}

Plugin::instance()->widgets_manager->register_widget_type( new motormania_Widget_Search_Banner );